<?php

namespace App\Modules\Finance\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'bank_account_id',
        'type',
        'amount',
        'currency',
        'transacted_at',
        'reference',
        'notes',
    ];

    protected $casts = [
        'transacted_at' => 'date',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}
