<?php

namespace App\Modules\Inventory\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class StockTransfer extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'doc_no',
        'from_warehouse_id',
        'from_bin_id',
        'to_warehouse_id',
        'to_bin_id',
        'status',
        'posted_at',
        'posted_by',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(StockTransferLine::class, 'transfer_id');
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function fromBin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'from_bin_id');
    }

    public function toBin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'to_bin_id');
    }
}
