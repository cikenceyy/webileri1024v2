<?php

namespace App\Cms\Http\Controllers\Admin;

use App\Cms\Support\CmsRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    public function __construct(protected CmsRepository $repository)
    {
    }

    public function index()
    {
        return view('cms::admin.index', [
            'pages' => config('cms.pages'),
        ]);
    }

    public function edit(string $page)
    {
        $pageConfig = config('cms.pages.' . $page);
        abort_unless($pageConfig, 404);

        return view('cms::admin.edit', [
            'pageKey' => $page,
            'pageConfig' => $pageConfig,
            'content' => [
                'tr' => $this->repository->read($page, 'tr'),
                'en' => $this->repository->read($page, 'en'),
            ],
            'seo' => [
                'tr' => $this->repository->seo($page, 'tr'),
                'en' => $this->repository->seo($page, 'en'),
            ],
            'scripts' => [
                'tr' => $this->repository->scripts($page, 'tr'),
                'en' => $this->repository->scripts($page, 'en'),
            ],
            'emails' => $this->repository->emails(),
        ]);
    }

    public function update(string $page, Request $request)
    {
        $pageConfig = config('cms.pages.' . $page);
        abort_unless($pageConfig, 404);

        $payload = $this->normalizePayload($pageConfig, $request);

        foreach (['tr', 'en'] as $locale) {
            $this->repository->write($page, $locale, [
                'blocks' => $payload['content'][$locale] ?? [],
                'seo' => $payload['seo'][$locale] ?? [],
                'scripts' => $payload['scripts'][$locale] ?? ['header' => null, 'footer' => null],
            ]);
        }

        $this->repository->updateEmails($payload['emails']);

        return redirect()->route('cms.admin.pages.edit', $page)->with('status', __('Saved successfully.'));
    }

    protected function normalizePayload(array $pageConfig, Request $request): array
    {
        $content = ['tr' => [], 'en' => []];
        foreach (['tr', 'en'] as $locale) {
            foreach ($pageConfig['blocks'] ?? [] as $blockKey => $definition) {
                if (!empty($definition['repeater'])) {
                    $items = $request->input("content.$locale.$blockKey", []);
                    $normalized = [];
                    foreach ($items as $index => $fields) {
                        $item = [];
                        foreach (($definition['fields'] ?? []) as $fieldKey => $meta) {
                            $inputKey = "content.$locale.$blockKey.$index.$fieldKey";
                            $item[$fieldKey] = $this->normalizeValue($inputKey, $meta['type'] ?? 'text', $request, $fields[$fieldKey] ?? null);
                        }
                        $normalized[] = $item;
                    }
                    $content[$locale][$blockKey] = $normalized;
                } else {
                    $fields = [];
                    foreach (($definition['fields'] ?? []) as $fieldKey => $meta) {
                        $inputKey = "content.$locale.$blockKey.$fieldKey";
                        $fields[$fieldKey] = $this->normalizeValue($inputKey, $meta['type'] ?? 'text', $request, $request->input($inputKey));
                    }
                    $content[$locale][$blockKey] = $fields;
                }
            }
        }

        $seo = [];
        foreach (['tr', 'en'] as $locale) {
            $seo[$locale] = [
                'meta_title' => $request->input("seo.$locale.meta_title"),
                'meta_description' => $request->input("seo.$locale.meta_description"),
                'og_image' => $this->normalizeValue("seo.$locale.og_image", 'image', $request, $request->input("seo.$locale.og_image")),
            ];
        }

        $scripts = [];
        foreach (['tr', 'en'] as $locale) {
            $scripts[$locale] = [
                'header' => $this->sanitizeScript($request->input("scripts.$locale.header")),
                'footer' => $this->sanitizeScript($request->input("scripts.$locale.footer")),
            ];
        }

        $emails = [
            'info_email' => $request->input('emails.info_email'),
            'notify_email' => $request->input('emails.notify_email'),
        ];

        return compact('content', 'seo', 'scripts', 'emails');
    }

    protected function normalizeValue(string $key, string $type, Request $request, $default = null)
    {
        if (in_array($type, ['image', 'file'], true) && $request->hasFile($key)) {
            return $this->storeFile($request->file($key));
        }

        if (in_array($type, ['text', 'textarea', 'link'], true)) {
            return $request->input($key);
        }

        return $default;
    }

    protected function sanitizeScript(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = trim($value);
        if (!preg_match('/^<script\b[^>]*>(.*?)<\/script>$/is', $value)) {
            return null;
        }

        if (preg_match('/on[a-z]+\s*=|<(?:img|iframe)/i', $value)) {
            return null;
        }

        return $value;
    }

    protected function storeFile($file): ?string
    {
        $path = 'tenants/' . $this->repository->companyId() . '/cms';
        $stored = $file->store($path, ['disk' => 'public']);

        if (!$stored) {
            return null;
        }

        return Storage::disk('public')->url($stored);
    }
}
