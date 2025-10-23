<?php

namespace App\Cms\Http\Controllers\Admin;

use App\Cms\Support\MediaUploader;
use App\Cms\Support\PreviewStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;

class PreviewController extends Controller
{
    public function __construct(
        protected PreviewStore $preview,
        protected MediaUploader $media
    ) {
    }

    public function apply(Request $request): JsonResponse
    {
        $token = $this->token($request);
        abort_unless($token, 400, 'Preview token missing.');

        $page = $request->input('page');
        $locale = $request->input('locale', 'tr');
        abort_unless(config('cms.pages.' . $page), 404);
        $locale = in_array($locale, ['tr', 'en'], true) ? $locale : 'tr';

        $payload = $request->input('payload', []);
        $sanitized = [
            'blocks' => Arr::get($payload, 'blocks', []),
            'seo' => Arr::get($payload, 'seo', []),
            'scripts' => Arr::get($payload, 'scripts', []),
        ];

        $this->preview->put($token, $page, $locale, $sanitized);

        return response()->json([
            'status' => 'applied',
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $token = $this->token($request);
        abort_unless($token, 400, 'Preview token missing.');

        $page = $request->input('page');
        abort_unless(config('cms.pages.' . $page), 404);

        $type = $request->input('type');

        $validated = $request->validate([
            'page' => ['required', 'string'],
            'locale' => ['required', 'in:tr,en'],
            'field' => ['required', 'string'],
            'type' => ['nullable', 'in:image,file'],
            'file' => array_merge(['required', 'file'], $this->fileRulesFor($page, $request->input('field'), $type)),
        ]);

        $stored = $this->media->store($request->file('file'), 'preview');

        abort_unless($stored, 500, 'Unable to store preview file.');

        return response()->json([
            'url' => $stored,
            'field' => $validated['field'],
        ]);
    }

    public function discard(Request $request): JsonResponse
    {
        $token = $this->token($request);
        abort_unless($token, 400, 'Preview token missing.');

        $page = $request->input('page');
        $locale = $request->input('locale');

        if ($page) {
            $this->preview->clear($token, $page, $locale);
        } else {
            $this->preview->flush($token);
        }

        return response()->json([
            'status' => 'cleared',
        ]);
    }

    protected function token(Request $request): ?string
    {
        return $request->header('X-CMS-Preview-Token')
            ?? $request->input('preview_token')
            ?? $this->preview->token();
    }

    protected function fileRulesFor(string $page, string $field, ?string $type = null): array
    {
        $meta = $this->fieldMeta($page, $field);
        $fieldType = $type ?: ($meta['type'] ?? 'image');

        if ($fieldType === 'file') {
            $limit = $meta['max'] ?? 10240;

            return ['mimes:pdf', 'max:' . $limit];
        }

        $limit = $meta['max'] ?? 2048;

        return ['image', 'mimes:jpg,jpeg,png,webp', 'max:' . $limit];
    }

    protected function fieldMeta(string $page, string $field): array
    {
        $segments = array_values(array_filter(explode('[', str_replace(']', '', $field))));

        if (!$segments) {
            return [];
        }

        $root = array_shift($segments);

        if ($root === 'content') {
            array_shift($segments); // locale
            $block = array_shift($segments);

            if (!$block) {
                return [];
            }

            $fieldKey = $this->resolveFieldKey($segments);

            if (!$fieldKey) {
                return [];
            }

            return Arr::get(config('cms.pages.' . $page . '.blocks.' . $block . '.fields'), $fieldKey, []);
        }

        if ($root === 'seo') {
            $fieldKey = $this->resolveFieldKey($segments);
            if ($fieldKey === 'og_image') {
                return ['type' => 'image', 'max' => 2048];
            }
        }

        return [];
    }

    protected function resolveFieldKey(array $segments): ?string
    {
        foreach (array_reverse($segments) as $segment) {
            if (!is_numeric($segment)) {
                return $segment;
            }
        }

        return null;
    }
}
