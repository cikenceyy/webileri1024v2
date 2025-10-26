<?php

namespace App\Modules\Drive\Http\Requests;

use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Drive\Http\Requests\Concerns\InteractsWithMediaUpload;
use App\Modules\Drive\Support\MediaUploadCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaRequest extends FormRequest
{
    use InteractsWithMediaUpload;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Media::class) ?? false;
    }

    public function rules(): array
    {
        $limits = $this->categoryLimits();
        $allowedExtensions = $limits->allowedExtensions();
        $allowedMimes = $limits->allowedMimes();

        return [
            'category' => [
                'required',
                Rule::in(MediaUploadCategory::allowedKeys()),
            ],
            'module' => [
                'required',
                Rule::in(Media::moduleKeys()),
            ],
            'file' => array_filter([
                'required',
                'file',
                'max:' . $limits->maxKilobytes(),
                $this->extensionRule($limits),
                $this->mimeRule($limits),
            ]),
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'Dosya uzantısı seçilen kategori için uygun değil.',
            'file.mimetypes' => 'Dosya türü seçilen kategori için uygun değil.',
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
            $limits = $this->categoryLimits($category);

            if ($file->getSize() > $limits->maxBytes()) {
                $validator->errors()->add('file', 'Dosya boyutu izin verilen sınırı aşıyor.');
            }

            $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: '');

            if (MediaUploadCategory::isForbiddenExtension($extension)) {
                $validator->errors()->add('file', 'Bu dosya uzantısı güvenlik nedeniyle yasaktır.');
            }

            $allowedExtensions = $limits->allowedExtensions();

            if ($allowedExtensions && ! in_array($extension, $allowedExtensions, true)) {
                $validator->errors()->add('file', 'Dosya uzantısı seçilen kategori için uygun değil.');
            }

            $mime = strtolower((string) $file->getClientMimeType());
            $allowedMimes = $limits->allowedMimes();

            if ($mime && $allowedMimes && ! in_array($mime, $allowedMimes, true)) {
                $validator->errors()->add('file', 'Dosya türü seçilen kategori için uygun değil.');
            }
        });
    }
}
