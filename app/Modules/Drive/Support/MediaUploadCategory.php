<?php

namespace App\Modules\Drive\Support;

use App\Modules\Drive\Domain\Models\Media;

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

    private string $module;

    private function __construct(string $module, string $key)
    {
        $this->module = $module;
        $this->key = $key;
    }

    public static function from(?string $category, ?string $module = null): self
    {
        $module = DriveStructure::normalizeModule($module ?: DriveStructure::defaultModule());
        $key = self::normalizeKey($category, $module);
        return new self($module, $key);
    }

    public static function fromMedia(?Media $media): self
    {
        return self::from($media?->category, $media?->module);
    }

    public static function allowedKeys(?string $module = null): array
    {
        $module = $module ? DriveStructure::normalizeModule($module) : null;

        return DriveStructure::folderKeys($module);
    }

    public static function normalizeKey(?string $category, ?string $module = null): string
    {
        return DriveStructure::normalizeFolderKey($category, $module);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function allowedExtensions(): array
    {
        return DriveStructure::folderExtensions($this->key);
    }

    public function allowedMimes(): array
    {
        return DriveStructure::folderMimes($this->key);
    }

    public function maxBytes(): int
    {
        return DriveStructure::folderMaxBytes($this->key);
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

}

