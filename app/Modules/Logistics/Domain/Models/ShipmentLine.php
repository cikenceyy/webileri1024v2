<?php

namespace App\Modules\Logistics\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Models\WarehouseBin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentLine extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'shipment_id',
        'product_id',
        'variant_id',
        'source_line_type',
        'source_line_id',
        'qty',
        'uom',
        'picked_qty',
        'packed_qty',
        'shipped_qty',
        'warehouse_id',
        'bin_id',
        'sort',
        'notes',
    ];

    protected $casts = [
        'qty' => 'float',
        'picked_qty' => 'float',
        'packed_qty' => 'float',
        'shipped_qty' => 'float',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'bin_id');
    }
}
