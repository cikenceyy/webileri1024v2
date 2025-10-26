<?php

namespace App\Modules\Drive\Support;

use App\Modules\Drive\Domain\Models\Media;
use Illuminate\Support\Arr;

class MediaUploadCategory
{
    private const FORBIDDEN_EXTENSIONS = [
        'php',
        'phar',
        'phtml',
        'pht',
        'exe',
        'sh',
        'bat',
        'cmd',
        'com',
        'dll',
    ];

    private string $key;

    private array $config;

    private function __construct(string $key, array $config)
    {
        $this->key = $key;
        $this->config = $config;
    }

    public static function from(?string $category): self
    {
        $key = self::normalizeKey($category);
        $config = config('drive.categories.' . $key, []);

        return new self($key, $config);
    }

    public static function fromMedia(?Media $media): self
    {
        return self::from($media?->category);
    }

    public static function allowedKeys(): array
    {
        return [
            Media::CATEGORY_DOCUMENTS,
            Media::CATEGORY_MEDIA_PRODUCTS,
            Media::CATEGORY_MEDIA_CATALOGS,
            Media::CATEGORY_PAGES,
        ];
    }

    public static function normalizeKey(?string $category): string
    {
        $category = strtolower((string) $category);

        return in_array($category, self::allowedKeys(), true)
            ? $category
            : Media::CATEGORY_DOCUMENTS;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function allowedExtensions(): array
    {
        return $this->normalizeList(Arr::get($this->config, 'ext', []));
    }

    public function allowedMimes(): array
    {
        return $this->normalizeList(Arr::get($this->config, 'mimes', []));
    }

    public function maxBytes(): int
    {
        $global = (int) config('drive.max_upload_bytes', 50 * 1024 * 1024);
        $categoryLimit = (int) Arr::get($this->config, 'max', $global);

        return (int) min($global, $categoryLimit);
    }

    public function maxKilobytes(): int
    {
        return self::bytesToKilobytes($this->maxBytes());
    }

    public static function bytesToKilobytes(int $bytes): int
    {
        return (int) max(1, (int) ceil($bytes / 1024));
    }

    public static function forbiddenExtensions(): array
    {
        return self::FORBIDDEN_EXTENSIONS;
    }

    public static function isForbiddenExtension(?string $extension): bool
    {
        if (! $extension) {
            return false;
        }

        return in_array(strtolower($extension), self::FORBIDDEN_EXTENSIONS, true);
    }

    private function normalizeList(array $items): array
    {
        $normalized = array_map(static fn ($value) => strtolower((string) $value), $items);

        return array_values(array_unique(array_filter($normalized)));
    }
}

