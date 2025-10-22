<?php

namespace App\Core\Support\Models;

use App\Modules\Drive\Domain\Models\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'theme_color',
        'logo_id',
        'drive_storage_limit_bytes',
    ];

    protected $casts = [
        'drive_storage_limit_bytes' => 'int',
    ];

    protected $attributes = [
        'drive_storage_limit_bytes' => 1_073_741_824,
    ];

    protected static function newFactory()
    {
        return \Database\Factories\Core\CompanyFactory::new();
    }

    public function domains(): HasMany
    {
        return $this->hasMany(CompanyDomain::class);
    }

    public function logo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_id');
    }

    public function getDriveStorageLimitBytesAttribute($value): int
    {
        $default = (int) config('drive.default_storage_limit_bytes', 1_073_741_824);

        return (int) ($value ?? $default);
    }
}
