<?php

namespace App\Modules\Marketing\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderLine extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'order_id',
        'product_id',
        'variant_id',
        'qty',
        'uom',
        'unit_price',
        'discount_pct',
        'tax_rate',
        'line_total',
        'notes',
    ];

    protected $casts = [
        'qty' => 'float',
        'unit_price' => 'decimal:4',
        'discount_pct' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
