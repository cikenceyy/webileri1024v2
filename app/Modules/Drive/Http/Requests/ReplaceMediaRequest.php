<?php

namespace App\Modules\Drive\Http\Requests;

use App\Modules\Drive\Domain\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class ReplaceMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $media = $this->route('media');

        if (! $media instanceof Media) {
            return false;
        }

        return $this->user()?->can('replace', $media) ?? false;
    }

    public function rules(): array
    {
        $media = $this->route('media');
        $category = $media instanceof Media ? $media->category : Media::CATEGORY_DOCUMENTS;
        $config = config('drive.categories.' . $category, []);
        $allowedExtensions = array_map('strtolower', Arr::get($config, 'ext', []));
        $allowedMimes = array_map('strtolower', Arr::get($config, 'mimes', []));
        $maxBytes = $this->resolveMaxBytes($config);

        return [
            'file' => array_filter([
                'required',
                'file',
                'max:' . $this->bytesToKilobytes($maxBytes),
                $allowedExtensions ? 'mimes:' . implode(',', $allowedExtensions) : null,
                $allowedMimes ? 'mimetypes:' . implode(',', $allowedMimes) : null,
            ]),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $file = $this->file('file');
            $media = $this->route('media');

            if (! $file || ! $media instanceof Media) {
                return;
            }

            $category = $media->category;
            $config = config('drive.categories.' . $category, []);
            $maxBytes = $this->resolveMaxBytes($config);
            $allowedExtensions = array_map('strtolower', Arr::get($config, 'ext', []));
            $allowedMimes = array_map('strtolower', Arr::get($config, 'mimes', []));
            $forbiddenExtensions = ['php', 'phar', 'phtml', 'pht', 'exe', 'sh', 'bat', 'cmd', 'com', 'dll'];

            if ($file->getSize() > $maxBytes) {
                $validator->errors()->add('file', 'Dosya boyutu izin verilen sınırı aşıyor.');
            }

            $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: '');

            if ($extension && in_array($extension, $forbiddenExtensions, true)) {
                $validator->errors()->add('file', 'Bu dosya uzantısı güvenlik nedeniyle yasaktır.');
            }

            if ($allowedExtensions && ! in_array($extension, $allowedExtensions, true)) {
                $validator->errors()->add('file', 'Dosya uzantısı seçilen kategori için uygun değil.');
            }

            $mime = strtolower((string) $file->getClientMimeType());

            if ($allowedMimes && $mime && ! in_array($mime, $allowedMimes, true)) {
                $validator->errors()->add('file', 'Dosya türü seçilen kategori için uygun değil.');
            }
        });
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
