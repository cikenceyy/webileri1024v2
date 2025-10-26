<?php

namespace App\Modules\Drive\Http\Requests;

use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Drive\Http\Requests\Concerns\InteractsWithMediaUpload;
use App\Modules\Drive\Support\DriveStructure;
use App\Modules\Drive\Support\MediaUploadCategory;
use Illuminate\Foundation\Http\FormRequest;

class ReplaceMediaRequest extends FormRequest
{
    use InteractsWithMediaUpload;

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
        $limits = $this->categoryLimits($media instanceof Media ? $media->category : null);
        $allowedExtensions = $limits->allowedExtensions();
        $allowedMimes = $limits->allowedMimes();

        return [
            'file' => array_filter([
                'required',
                'file',
                'max:' . $limits->maxKilobytes(),
                $this->extensionRule($limits),
                $this->mimeRule($limits),
            ]),
        ];
    }

    protected function prepareForValidation(): void
    {
        $media = $this->route('media');

        if ($media instanceof Media) {
            $this->merge([
                'module' => DriveStructure::normalizeModule($media->module),
                'category' => $media->category,
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $file = $this->file('file');
            $media = $this->route('media');

            if (! $file || ! $media instanceof Media) {
                return;
            }

            $limits = $this->categoryLimits($media->category);
            $allowedExtensions = $limits->allowedExtensions();
            $allowedMimes = $limits->allowedMimes();

            if ($file->getSize() > $limits->maxBytes()) {
                $validator->errors()->add('file', 'Dosya boyutu izin verilen sınırı aşıyor.');
            }

            $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: '');

            if (MediaUploadCategory::isForbiddenExtension($extension)) {
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
}
