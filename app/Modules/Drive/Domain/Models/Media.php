<?php

namespace App\Modules\Drive\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media extends Model
{
    use BelongsToCompany;
    use HasFactory;
    use SoftDeletes;

    public const CATEGORY_DOCUMENTS = 'documents';
    public const CATEGORY_MEDIA_PRODUCTS = 'media_products';
    public const CATEGORY_MEDIA_CATALOGS = 'media_catalogs';
    public const CATEGORY_PAGES = 'pages';

    protected $fillable = [
        'company_id',
        'category',
        'disk',
        'path',
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

    protected static function booted(): void
    {
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
