<?php

namespace App\Modules\Production\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Models\WarehouseBin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderReceipt extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'work_order_id',
        'warehouse_id',
        'bin_id',
        'qty',
        'posted_at',
        'posted_by',
    ];

    protected $casts = [
        'qty' => 'float',
        'posted_at' => 'datetime',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
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
