<?php

namespace App\Modules\Production\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Models\WarehouseBin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomItem extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'bom_id',
        'component_product_id',
        'component_variant_id',
        'qty_per',
        'wastage_pct',
        'default_warehouse_id',
        'default_bin_id',
        'sort',
    ];

    protected $casts = [
        'qty_per' => 'float',
        'wastage_pct' => 'float',
    ];

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }

    public function componentVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'component_variant_id');
    }

    public function defaultWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'default_warehouse_id');
    }

    public function defaultBin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'default_bin_id');
    }
}
