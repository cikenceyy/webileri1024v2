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
        'logo_id',
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
}
