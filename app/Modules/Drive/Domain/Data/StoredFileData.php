<?php

namespace App\Modules\Drive\Domain\Data;

use Illuminate\Contracts\Support\Arrayable;

class StoredFileData implements Arrayable
{
    public function __construct(
        public readonly string $disk,
        public readonly string $path,
        public readonly string $visibility,
        public readonly string $originalName,
        public readonly string $mime,
        public readonly string $extension,
        public readonly int $size,
        public readonly ?string $hash,
        public readonly ?int $width,
        public readonly ?int $height,
        public readonly string $filename,
        public readonly string $directory,
        public readonly string $ulid,
    ) {
    }

    public function toArray(): array
    {
        return [
            'disk' => $this->disk,
            'path' => $this->path,
            'visibility' => $this->visibility,
            'original_name' => $this->originalName,
            'mime' => $this->mime,
            'ext' => $this->extension,
            'size' => $this->size,
            'sha256' => $this->hash,
            'width' => $this->width,
            'height' => $this->height,
            'filename' => $this->filename,
            'directory' => $this->directory,
            'ulid' => $this->ulid,
        ];
    }
}
