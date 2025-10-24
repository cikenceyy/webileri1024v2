<?php

namespace App\Modules\Drive\Http\Requests;

use App\Modules\Drive\Domain\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreManyMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Media::class) ?? false;
    }

    public function rules(): array
    {
        $category = $this->validatedCategory();
        $config = config('drive.categories.' . $category, []);
        $allowedExtensions = array_map('strtolower', Arr::get($config, 'ext', []));
        $allowedMimes = array_map('strtolower', Arr::get($config, 'mimes', []));
        $maxBytes = $this->resolveMaxBytes($config);

        return [
            'category' => [
                'required',
                Rule::in([
                    Media::CATEGORY_DOCUMENTS,
                    Media::CATEGORY_MEDIA_PRODUCTS,
                    Media::CATEGORY_MEDIA_CATALOGS,
                    Media::CATEGORY_PAGES,
                ]),
            ],
            'module' => [
                'required',
                Rule::in(Media::moduleKeys()),
            ],
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => array_filter([
                'required',
                'file',
                'max:' . $this->bytesToKilobytes($maxBytes),
                $allowedExtensions ? 'mimes:' . implode(',', $allowedExtensions) : null,
                $allowedMimes ? 'mimetypes:' . implode(',', $allowedMimes) : null,
            ]),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('category')) {
            $this->merge([
                'category' => strtolower((string) $this->input('category')),
            ]);
        }

        $this->merge([
            'module' => strtolower((string) ($this->input('module') ?: Media::MODULE_DEFAULT)),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $files = $this->file('files', []);

            if (! is_array($files) || ! count($files)) {
                return;
            }

            $category = $this->validatedCategory();
            $config = config('drive.categories.' . $category, []);
            $maxBytes = $this->resolveMaxBytes($config);
            $allowedExtensions = array_map('strtolower', Arr::get($config, 'ext', []));
            $allowedMimes = array_map('strtolower', Arr::get($config, 'mimes', []));
            $forbiddenExtensions = ['php', 'phar', 'phtml', 'pht', 'exe', 'sh', 'bat', 'cmd', 'com', 'dll'];

            foreach ($files as $index => $file) {
                if (! $file) {
                    $validator->errors()->add("files.$index", 'Dosya yüklenemedi.');
                    continue;
                }

                if ($file->getSize() > $maxBytes) {
                    $validator->errors()->add("files.$index", 'Dosya boyutu izin verilen sınırı aşıyor.');
                }

                $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: '');

                if ($extension && in_array($extension, $forbiddenExtensions, true)) {
                    $validator->errors()->add("files.$index", 'Bu dosya uzantısı güvenlik nedeniyle yasaktır.');
                }

                if ($allowedExtensions && ! in_array($extension, $allowedExtensions, true)) {
                    $validator->errors()->add("files.$index", 'Dosya uzantısı seçilen kategori için uygun değil.');
                }

                $mime = strtolower((string) $file->getClientMimeType());

                if ($allowedMimes && $mime && ! in_array($mime, $allowedMimes, true)) {
                    $validator->errors()->add("files.$index", 'Dosya türü seçilen kategori için uygun değil.');
                }
            }
        });
    }

    protected function validatedCategory(): string
    {
        $category = strtolower((string) $this->input('category'));

        $allowed = [
            Media::CATEGORY_DOCUMENTS,
            Media::CATEGORY_MEDIA_PRODUCTS,
            Media::CATEGORY_MEDIA_CATALOGS,
            Media::CATEGORY_PAGES,
        ];

        return in_array($category, $allowed, true) ? $category : Media::CATEGORY_DOCUMENTS;
    }

    protected function resolveMaxBytes(array $categoryConfig): int
    {
        $global = (int) config('drive.max_upload_bytes', 50 * 1024 * 1024);
        $categoryLimit = (int) Arr::get($categoryConfig, 'max', $global);

        return (int) min($global, $categoryLimit);
    }

    protected function bytesToKilobytes(int $bytes): int
    {
        return (int) max(1, (int) ceil($bytes / 1024));
    }
}
