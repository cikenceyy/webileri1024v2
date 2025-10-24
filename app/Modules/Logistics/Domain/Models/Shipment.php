<?php

namespace App\Modules\Logistics\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Marketing\Domain\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'doc_no',
        'customer_id',
        'source_type',
        'source_id',
        'status',
        'warehouse_id',
        'packages_count',
        'gross_weight',
        'net_weight',
        'shipped_at',
        'notes',
    ];

    protected $casts = [
        'packages_count' => 'int',
        'gross_weight' => 'decimal:3',
        'net_weight' => 'decimal:3',
        'shipped_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ShipmentLine::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
