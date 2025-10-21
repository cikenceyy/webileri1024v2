<?php

namespace App\Modules\Inventory\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class StockMovement extends Model
{
    use BelongsToCompany;

    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';

    public const REASONS = ['purchase', 'sale', 'transfer', 'adjustment', 'opening', 'return'];

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'product_id',
        'variant_id',
        'direction',
        'qty',
        'unit_cost',
        'reason',
        'ref_type',
        'ref_id',
        'note',
        'moved_by',
        'moved_at',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'unit_cost' => 'decimal:4',
        'moved_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function mover(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'moved_by');
    }

    public function scopeForProduct(Builder $query, int $productId, ?int $variantId = null): Builder
    {
        $query->where('product_id', $productId);

        if ($variantId !== null) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }

        return $query;
    }

    public function scopeDateRange(Builder $query, ?Carbon $from, ?Carbon $to): Builder
    {
        if ($from) {
            $query->where('moved_at', '>=', $from->startOfDay());
        }

        if ($to) {
            $query->where('moved_at', '<=', $to->endOfDay());
        }

        return $query;
    }

    public function getValueAttribute(): float
    {
        $unitCost = $this->unit_cost ?? 0.0;
        $qty = (float) $this->qty;

        return $unitCost * $qty * ($this->direction === self::DIRECTION_IN ? 1 : -1);
    }
}
