<?php

namespace App\Modules\Drive\Http\Requests;

use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Drive\Http\Requests\Concerns\InteractsWithMediaUpload;
use App\Modules\Drive\Support\DriveStructure;
use App\Modules\Drive\Support\MediaUploadCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManyMediaRequest extends FormRequest
{
    use InteractsWithMediaUpload;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Media::class) ?? false;
    }

    public function rules(): array
    {
        $module = DriveStructure::normalizeModule($this->input('module'));
        $limits = $this->categoryLimits();
        $allowedExtensions = $limits->allowedExtensions();
        $allowedMimes = $limits->allowedMimes();

        return [
            'category' => [
                'required',
                Rule::in(MediaUploadCategory::allowedKeys($module)),
            ],
            'module' => [
                'required',
                Rule::in(DriveStructure::moduleKeys()),
            ],
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => array_filter([
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
        if ($this->has('category')) {
            $this->merge([
                'category' => strtolower((string) $this->input('category')),
            ]);
        }

        $this->merge([
            'module' => DriveStructure::normalizeModule($this->input('module') ?: DriveStructure::defaultModule()),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $files = $this->file('files', []);

            if (! is_array($files) || ! count($files)) {
                return;
            }

            $module = DriveStructure::normalizeModule($this->input('module'));
            $category = $this->validatedCategory();
            if (! DriveStructure::moduleAllowsFolder($module, $category)) {
                $validator->errors()->add('category', 'Seçilen klasör bu modül için uygun değil.');

                return;
            }
            $limits = $this->categoryLimits($category);
            $allowedExtensions = $limits->allowedExtensions();
            $allowedMimes = $limits->allowedMimes();

            foreach ($files as $index => $file) {
                if (! $file) {
                    $validator->errors()->add("files.$index", 'Dosya yüklenemedi.');
                    continue;
                }

                if ($file->getSize() > $limits->maxBytes()) {
                    $validator->errors()->add("files.$index", 'Dosya boyutu izin verilen sınırı aşıyor.');
                }

                $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: '');

                if (MediaUploadCategory::isForbiddenExtension($extension)) {
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
}
