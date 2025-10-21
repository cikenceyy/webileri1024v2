<?php

namespace App\Modules\Marketing\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'customer_id',
        'type',
        'line1',
        'line2',
        'line3',
        'line4',
        'city',
        'state',
        'country',
        'postal_code',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'bool',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
