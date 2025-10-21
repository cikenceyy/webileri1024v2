<?php

namespace App\Modules\Finance\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApInvoiceLine extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'ap_invoice_id',
        'description',
        'qty',
        'unit',
        'unit_price',
        'amount',
        'source_type',
        'source_uuid',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ApInvoice::class, 'ap_invoice_id');
    }
}
