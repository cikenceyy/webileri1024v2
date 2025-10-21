<?php

namespace App\Modules\Production\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Marketing\Domain\Models\OrderLine;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'order_id',
        'order_line_id',
        'product_id',
        'variant_id',
        'work_order_no',
        'qty',
        'unit',
        'status',
        'planned_start_date',
        'due_date',
        'notes',
        'closed_at',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'planned_start_date' => 'date',
        'due_date' => 'date',
        'closed_at' => 'datetime',
    ];

    public static function generateNo(int $companyId): string
    {
        return next_number('WO', [
            'prefix' => 'WO',
            'reset_period' => 'monthly',
        ], $companyId);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderLine(): BelongsTo
    {
        return $this->belongsTo(OrderLine::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function materialIssues(): HasMany
    {
        return $this->hasMany(WoMaterialIssue::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(WoReceipt::class);
    }
}
