<?php

namespace App\Modules\Procurement\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'supplier_id',
        'po_number',
        'status',
        'total',
        'currency',
        'approved_at',
        'closed_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'approved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PoLine::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(Grn::class, 'purchase_order_id');
    }

    public static function generateNumber(int $companyId): string
    {
        return next_number('PO', [
            'prefix' => 'PO',
        ], $companyId);
    }
}
