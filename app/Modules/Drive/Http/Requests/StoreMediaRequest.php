<?php

namespace App\Modules\Drive\Http\Requests;

use App\Modules\Drive\Domain\Models\Media;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Media::class) ?? false;
    }

    public function rules(): array
    {
        $categories = config('drive.categories', []);
        $category = $this->validatedCategory();
        $categoryConfig = $categories[$category] ?? [];
        $allowedExtensions = array_map('strtolower', $categoryConfig['ext'] ?? []);
        $allowedMimes = array_map('strtolower', $categoryConfig['mimes'] ?? []);
        $maxBytes = $this->resolveMaxBytes($categoryConfig);

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
            'file' => array_filter([
                'required',
                'file',
                'max:' . $this->bytesToKilobytes($maxBytes),
                $allowedExtensions ? 'mimes:' . implode(',', $allowedExtensions) : null,
                $allowedMimes ? 'mimetypes:' . implode(',', $allowedMimes) : null,
            ]),
        ];
    }

    public function messages(): array
    {
        $category = $this->validatedCategory();
        $config = config('drive.categories.' . $category, []);
        $extensions = Arr::get($config, 'ext', []);
        $mimes = Arr::get($config, 'mimes', []);

        $extensionList = $extensions ? implode(', ', array_map(fn ($ext) => '.' . strtolower($ext), $extensions)) : null;
        $mimeList = $mimes ? implode(', ', array_map('strtolower', $mimes)) : null;

        return [
            'file.mimes' => $extensionList
                ? sprintf('Dosya uzantısı desteklenmiyor. Kabul edilen uzantılar: %s.', $extensionList)
                : 'Dosya uzantısı desteklenmiyor.',
            'file.mimetypes' => $mimeList
                ? sprintf('Dosya türü desteklenmiyor. Beklenen MIME türleri: %s.', $mimeList)
                : 'Dosya türü desteklenmiyor.',
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
            $file = $this->file('file');

            if (! $file) {
                return;
            }

            $category = $this->validatedCategory();
            $categoryConfig = config('drive.categories.' . $category, []);
            $maxBytes = $this->resolveMaxBytes($categoryConfig);

            if ($file->getSize() > $maxBytes) {
                $validator->errors()->add(
                    'file',
                    sprintf(
                        'Dosya boyutu izin verilen sınırı aşıyor. Maksimum: %s.',
                        $this->formatBytes($maxBytes)
                    ),
                );
            }

            $forbiddenExtensions = ['php', 'phar', 'phtml', 'pht', 'exe', 'sh', 'bat', 'cmd', 'com', 'dll'];
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: '');

            if ($extension && in_array($extension, $forbiddenExtensions, true)) {
                $validator->errors()->add('file', sprintf('".%s" uzantısı güvenlik nedeniyle yasaktır.', $extension));
            }

            $allowedExtensions = array_map('strtolower', Arr::get($categoryConfig, 'ext', []));

            if ($allowedExtensions && ! in_array($extension, $allowedExtensions, true)) {
                $extLabel = $extension ? '.' . $extension : __('bilinmiyor');
                $validator->errors()->add(
                    'file',
                    sprintf(
                        '"%s" uzantısı desteklenmiyor. Kabul edilen uzantılar: %s.',
                        $extLabel,
                        implode(', ', array_map(fn ($ext) => '.' . $ext, $allowedExtensions))
                    ),
                );
            }

            $mime = strtolower((string) $file->getClientMimeType());
            $allowedMimes = array_map('strtolower', Arr::get($categoryConfig, 'mimes', []));

            if ($mime && $allowedMimes && ! in_array($mime, $allowedMimes, true)) {
                $validator->errors()->add(
                    'file',
                    sprintf(
                        '%s MIME türü desteklenmiyor. Beklenen türler: %s.',
                        $mime,
                        implode(', ', $allowedMimes)
                    ),
                );
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

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_073_741_824) {
            return sprintf('%.2f GB', $bytes / 1_073_741_824);
        }

        if ($bytes >= 1_048_576) {
            return sprintf('%.2f MB', $bytes / 1_048_576);
        }

        if ($bytes >= 1024) {
            return sprintf('%.2f KB', $bytes / 1024);
        }

        return sprintf('%d B', $bytes);
    }
}
