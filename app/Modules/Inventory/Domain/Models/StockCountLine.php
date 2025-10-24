<?php

namespace App\Modules\Inventory\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCountLine extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'count_id',
        'product_id',
        'qty_expected',
        'qty_counted',
        'diff_cached',
    ];

    protected $casts = [
        'qty_expected' => 'float',
        'qty_counted' => 'float',
        'diff_cached' => 'float',
    ];

    public function count(): BelongsTo
    {
        return $this->belongsTo(StockCount::class, 'count_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
