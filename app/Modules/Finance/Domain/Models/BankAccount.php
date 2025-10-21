<?php

namespace App\Modules\Finance\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'account_no',
        'currency',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default' => 'bool',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }
}
