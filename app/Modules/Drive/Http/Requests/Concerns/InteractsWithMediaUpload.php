<?php

namespace App\Modules\Drive\Http\Requests\Concerns;

use App\Modules\Drive\Support\MediaUploadCategory;

trait InteractsWithMediaUpload
{
    protected function categoryLimits(?string $category = null): MediaUploadCategory
    {
        return MediaUploadCategory::from(
            $category ?? $this->input('category'),
            $this->input('module')
        );
    }

    protected function validatedCategory(): string
    {
        return $this->categoryLimits()->key();
    }

    protected function extensionRule(MediaUploadCategory $limits): ?string
    {
        $extensions = $limits->allowedExtensions();

        return $extensions ? 'mimes:' . implode(',', $extensions) : null;
    }

    protected function mimeRule(MediaUploadCategory $limits): ?string
    {
        $mimes = $limits->allowedMimes();

        return $mimes ? 'mimetypes:' . implode(',', $mimes) : null;
    }
}

