<?php

namespace App\Cms\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaUploader
{
    public function __construct(protected CmsRepository $repository)
    {
    }

    public function store(UploadedFile $file, string $context = 'content'): ?string
    {
        $basePath = 'tenants/' . $this->repository->companyId() . '/cms';

        if ($context === 'preview') {
            $basePath .= '/previews';
        }

        $stored = $file->store($basePath, ['disk' => 'public']);

        if (!$stored) {
            return null;
        }

        return Storage::disk('public')->url($stored);
    }
}
