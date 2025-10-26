<?php

namespace App\Modules\Drive\Domain;

use App\Modules\Drive\Domain\Data\StoredFileData;
use App\Modules\Drive\Domain\Models\Media;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriveStorage
{
    public function __construct(private readonly FilesystemManager $filesystems)
    {
    }

    public function diskName(): string
    {
        return config('drive.disk', config('filesystems.default'));
    }

    public function filesystem(?string $disk = null): Filesystem|FilesystemAdapter
    {
        return $this->filesystems->disk($disk ?? $this->diskName());
    }

    public function put(UploadedFile $file, int $companyId, string $module, string $folder, ?string $visibility = null): StoredFileData
    {
        $visibility = $visibility ?: 'private';
        $disk = $this->diskName();
        $filesystem = $this->filesystem($disk);

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $originalName = $file->getClientOriginalName();
        $filenameSlug = Str::of(pathinfo($originalName, PATHINFO_FILENAME) ?: 'file')
            ->slug('-')
            ->limit(60, '')
            ->lower()
            ->whenEmpty(fn () => 'file')
            ->value();
        $ulid = (string) Str::ulid();
        $today = now();
        $segments = array_filter([
            trim(str_replace('{company_id}', (string) $companyId, config('drive.path_prefix', 'companies/{company_id}/drive')), '/'),
            trim($module ?: 'general', '/'),
            trim($folder ?: 'documents', '/'),
            $today->format('Y'),
            $today->format('m'),
            $today->format('d'),
        ]);
        $directory = implode('/', $segments);
        $filename = sprintf('%s_%s.%s', $ulid, $filenameSlug, $extension);
        $path = trim($directory . '/' . $filename, '/');

        $filesystem->putFileAs($directory, $file, $filename, ['visibility' => $visibility]);

        $hash = null;
        try {
            $hash = hash_file('sha256', (string) $file->getRealPath()) ?: null;
        } catch (\Throwable $exception) {
            report($exception);
        }

        $mime = strtolower((string) $file->getClientMimeType() ?: 'application/octet-stream');
        $width = null;
        $height = null;
        if (Str::startsWith($mime, 'image/')) {
            try {
                $info = @getimagesize($file->getRealPath());
                if (is_array($info)) {
                    $width = $info[0] ?? null;
                    $height = $info[1] ?? null;
                }
            } catch (\Throwable) {
                $width = $height = null;
            }
        }

        return new StoredFileData(
            disk: $disk,
            path: $path,
            visibility: $visibility,
            originalName: $originalName,
            mime: $mime,
            extension: $extension,
            size: $file->getSize() ?: 0,
            hash: $hash,
            width: $width,
            height: $height,
            filename: $filename,
            directory: $directory,
            ulid: $ulid,
        );
    }

    public function temporaryUrl(Media $media, ?int $seconds = null): ?string
    {
        $seconds = $seconds ?: (int) config('drive.presign_ttl_seconds', 600);
        $disk = $media->disk ?: $this->diskName();
        $filesystem = $this->filesystem($disk);

        try {
            if (method_exists($filesystem, 'temporaryUrl')) {
                return $filesystem->temporaryUrl($media->path, now()->addSeconds($seconds), [
                    'ResponseContentDisposition' => 'attachment; filename="' . addslashes($media->original_name) . '"',
                ]);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        try {
            if (method_exists($filesystem, 'url')) {
                return $filesystem->url($media->path);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return null;
    }

    public function download(Media $media, ?string $name = null): StreamedResponse
    {
        $disk = $media->disk ?: $this->diskName();
        $filesystem = $this->filesystem($disk);
        $name = $name ?: $media->original_name;

        return $filesystem->download($media->path, $name, ['Content-Type' => $media->mime]);
    }

    public function delete(Media $media): void
    {
        $disk = $media->disk ?: $this->diskName();
        $filesystem = $this->filesystem($disk);

        $paths = array_filter([$media->path, $media->thumb_path]);
        if (! $paths) {
            return;
        }

        try {
            $filesystem->delete($paths);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
