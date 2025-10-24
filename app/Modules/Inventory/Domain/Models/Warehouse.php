<?php

namespace App\Modules\Inventory\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use BelongsToCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'is_default',
        'status',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'bool',
        'is_active' => 'bool',
    ];

    public function bins(): HasMany
    {
        return $this->hasMany(WarehouseBin::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $builder) use ($term) {
            $builder->where('code', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%");
        });
    }
}
