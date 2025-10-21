<?php

namespace App\Modules\Finance\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Marketing\Domain\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receipt extends Model
{
    use BelongsToCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'receipt_no',
        'receipt_date',
        'currency',
        'amount',
        'bank_account_id',
        'allocated_total',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'receipt_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }

    public function refreshAllocatedTotal(): void
    {
        $allocated = $this->allocations()->sum('amount');
        $this->forceFill(['allocated_total' => round($allocated, 2)])->save();
    }
}
