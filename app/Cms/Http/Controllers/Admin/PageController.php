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
        return view('admin.cms.index', [
            'pages' => config('cms.pages'),
        ]);
    }

    public function edit(string $page)
    {
        $pageConfig = config('cms.pages.' . $page);
        abort_unless($pageConfig, 404);

        return view('admin.cms.edit', [
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

        $this->validateRequest($pageConfig, $request);

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

    protected function validateRequest(array $pageConfig, Request $request): void
    {
        $rules = [];
        $attributes = [];
        foreach (['tr', 'en'] as $locale) {
            foreach ($pageConfig['blocks'] ?? [] as $blockKey => $definition) {
                if (!empty($definition['repeater'])) {
                    foreach (($definition['fields'] ?? []) as $fieldKey => $meta) {
                        $rules["content.$locale.$blockKey.*.$fieldKey"] = $this->rulesForField($meta);
                        $attributes["content.$locale.$blockKey.*.$fieldKey"] = $this->attributeLabel($definition, $meta, $locale);
                    }
                    continue;
                }

                foreach (($definition['fields'] ?? []) as $fieldKey => $meta) {
                    $rules["content.$locale.$blockKey.$fieldKey"] = $this->rulesForField($meta);
                    $attributes["content.$locale.$blockKey.$fieldKey"] = $this->attributeLabel($definition, $meta, $locale);
                }
            }

            $rules["seo.$locale.meta_title"] = ['nullable', 'string', 'max:180'];
            $rules["seo.$locale.meta_description"] = ['nullable', 'string', 'max:260'];
            $rules["seo.$locale.og_image"] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
            $rules["seo.$locale.og_image_remove"] = ['nullable', 'boolean'];
            $rules["scripts.$locale.header"] = ['nullable', 'string'];
            $rules["scripts.$locale.footer"] = ['nullable', 'string'];
        }

        $rules['emails.info_email'] = ['nullable', 'email'];
        $rules['emails.notify_email'] = ['nullable', 'email'];

        $request->validate($rules, [], $attributes);
    }

    protected function rulesForField(array $meta): array
    {
        $type = $meta['type'] ?? 'text';

        return match ($type) {
            'textarea', 'multiline' => ['nullable', 'string', 'max:' . ($meta['max_length'] ?? 2000)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . ($meta['max'] ?? 2048)],
            'file' => ['nullable', 'mimes:pdf', 'max:' . ($meta['max'] ?? 10240)],
            'link' => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/|\/).*/'],
            default => ['nullable', 'string', 'max:' . ($meta['max_length'] ?? 255)],
        };
    }

    protected function attributeLabel(array $blockDefinition, array $fieldMeta, string $locale): string
    {
        $blockLabel = $blockDefinition['label'] ?? 'Block';
        $fieldLabel = $fieldMeta['label'] ?? 'Field';

        return sprintf('%s (%s) — %s', $blockLabel, strtoupper($locale), $fieldLabel);
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
                        $hasValue = array_filter($item, static fn ($value) => !is_null($value) && $value !== '' && $value !== []);
                        if ($hasValue) {
                            $normalized[] = $item;
                        }
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
        if ($request->boolean($key . '_remove')) {
            return null;
        }

        if (in_array($type, ['image', 'file'], true) && $request->hasFile($key)) {
            return $this->storeFile($request->file($key));
        }

        if (in_array($type, ['text', 'textarea', 'link', 'multiline'], true)) {
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
        if (!preg_match('/^<script\b[^>]*src="[^"]+"[^>]*>\s*<\/script>$/i', $value)) {
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
