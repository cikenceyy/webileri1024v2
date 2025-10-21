<?php

namespace App\Modules\Procurement\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Inventory\Domain\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PoLine extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'purchase_order_id',
        'product_id',
        'description',
        'qty_ordered',
        'unit',
        'unit_price',
        'line_total',
        'uuid',
    ];

    protected $casts = [
        'qty_ordered' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function grnLines(): HasMany
    {
        return $this->hasMany(GrnLine::class, 'po_line_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $line): void {
            if (! $line->uuid) {
                $line->uuid = (string) Str::uuid();
            }
        });
    }
}
