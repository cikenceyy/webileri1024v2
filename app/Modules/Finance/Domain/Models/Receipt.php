<?php

namespace App\Modules\Finance\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Marketing\Domain\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receipt extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'customer_id',
        'doc_no',
        'received_at',
        'amount',
        'method',
        'reference',
        'notes',
    ];

    protected $casts = [
        'received_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(ReceiptApplication::class);
    }

    public function appliedTotal(): float
    {
        return (float) $this->applications()->sum('amount');
    }

    public function availableAmount(): float
    {
        return max(0.0, (float) $this->amount - $this->appliedTotal());
    }
}
