<?php

namespace App\Modules\Drive\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Modules\Drive\Support\DriveStructure;

class Media extends Model
{
    use BelongsToCompany;
    use HasFactory;
    use SoftDeletes;

    public const TYPE_DOCUMENT = 'document';
    public const TYPE_MEDIA = 'media';

    public const MODULE_CMS = 'cms';
    public const MODULE_MARKETING = 'marketing';
    public const MODULE_FINANCE = 'finance';
    public const MODULE_LOGISTICS = 'logistics';
    public const MODULE_INVENTORY = 'inventory';
    public const MODULE_PRODUCTION = 'production';
    public const MODULE_HR = 'hr';

    public const MODULE_DEFAULT = self::MODULE_CMS;

    public const MODULE_LABELS = [
        self::MODULE_CMS => 'CMS',
        self::MODULE_MARKETING => 'Marketing',
        self::MODULE_FINANCE => 'Finance',
        self::MODULE_LOGISTICS => 'Logistics',
        self::MODULE_INVENTORY => 'Inventory',
        self::MODULE_PRODUCTION => 'Production',
        self::MODULE_HR => 'HR',
    ];

    protected $fillable = [
        'company_id',
        'category',
        'module',
        'disk',
        'path',
        'uuid',
        'thumb_path',
        'original_name',
        'mime',
        'ext',
        'size',
        'sha256',
        'width',
        'height',
        'is_important',
        'uploaded_by',
    ];

    protected $casts = [
        'is_important' => 'boolean',
        'size' => 'int',
        'width' => 'int',
        'height' => 'int',
    ];

    public static function moduleOptions(): array
    {
        return DriveStructure::moduleOptions();
    }

    public static function moduleKeys(): array
    {
        return array_keys(self::moduleOptions());
    }

    public static function moduleLabel(?string $module): string
    {
        $module = $module ?: self::MODULE_DEFAULT;

        return DriveStructure::moduleOptions()[$module] ?? Str::of($module)->headline();
    }

    public static function folderKeys(): array
    {
        return DriveStructure::folderKeys();
    }

    public static function documentCategories(): array
    {
        return DriveStructure::foldersByType(self::TYPE_DOCUMENT);
    }

    public static function mediaCategories(): array
    {
        return DriveStructure::foldersByType(self::TYPE_MEDIA);
    }

    public static function categoryType(string $category): string
    {
        return DriveStructure::folderType($category) === self::TYPE_DOCUMENT
            ? self::TYPE_DOCUMENT
            : self::TYPE_MEDIA;
    }

    protected static function booted(): void
    {
        static::creating(function (self $media): void {
            if (! $media->uuid) {
                $media->uuid = (string) Str::uuid();
            }
        });

        static::deleted(function (self $media): void {
            if (! $media->isForceDeleting()) {
                return;
            }

            if ($media->disk) {
                try {
                    $paths = array_filter([$media->path, $media->thumb_path]);

                    if ($paths) {
                        Storage::disk($media->disk)->delete($paths);
                    }
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class), 'uploaded_by');
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30))->orderByDesc('created_at');
    }

    public function isImage(): bool
    {
        if ($this->mime && Str::startsWith($this->mime, 'image/')) {
            return true;
        }

        return in_array($this->ext, ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'bmp'], true);
    }

    public function humanSize(): string
    {
        $bytes = $this->size ?? 0;

        if ($bytes >= 1_073_741_824) {
            return sprintf('%.2f GB', $bytes / 1_073_741_824);
        }

        if ($bytes >= 1_048_576) {
            return sprintf('%.2f MB', $bytes / 1_048_576);
        }

        if ($bytes >= 1024) {
            return sprintf('%.2f KB', $bytes / 1024);
        }

        return sprintf('%d B', $bytes);
    }
}
