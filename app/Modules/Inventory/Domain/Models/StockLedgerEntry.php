<?php

namespace App\Modules\Inventory\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLedgerEntry extends Model
{
    use BelongsToCompany;

    protected $table = 'stock_ledger';

    protected $fillable = [
        'company_id',
        'product_id',
        'warehouse_id',
        'bin_id',
        'qty_in',
        'qty_out',
        'reason',
        'ref_type',
        'ref_id',
        'doc_no',
        'dated_at',
    ];

    protected $casts = [
        'qty_in' => 'float',
        'qty_out' => 'float',
        'dated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
