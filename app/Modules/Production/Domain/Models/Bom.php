<?php

namespace App\Modules\Production\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bom extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'product_id',
        'variant_id',
        'code',
        'version',
        'output_qty',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'output_qty' => 'float',
        'is_active' => 'bool',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BomItem::class);
    }
}
