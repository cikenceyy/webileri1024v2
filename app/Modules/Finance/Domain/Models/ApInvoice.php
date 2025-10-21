<?php

namespace App\Modules\Finance\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Procurement\Domain\Models\Grn;
use App\Modules\Procurement\Domain\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApInvoice extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'purchase_order_id',
        'grn_id',
        'supplier_id',
        'status',
        'reference',
        'invoice_date',
        'due_date',
        'currency',
        'subtotal',
        'tax_total',
        'total',
        'balance_due',
        'expected_total',
        'price_variance_amount',
        'has_price_variance',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'expected_total' => 'decimal:2',
        'price_variance_amount' => 'decimal:2',
        'has_price_variance' => 'boolean',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(ApInvoiceLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ApPayment::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(Grn::class, 'grn_id');
    }

    public function refreshTotals(): void
    {
        $this->loadMissing('lines');

        $subtotal = $this->lines->sum(fn (ApInvoiceLine $line): float => (float) $line->amount);
        $tax = (float) $this->tax_total;
        $total = round($subtotal + $tax, 2);

        $paid = $this->payments()->sum('amount');
        $balance = max(0, round($total - (float) $paid, 2));

        $expected = (float) $this->expected_total;
        $difference = round($total - $expected, 2);
        $tolerancePercent = (float) config('finance.ap_price_tolerance_percent', 2);
        $toleranceAbsolute = (float) config('finance.ap_price_tolerance_absolute', 0);
        $percentAllowance = $expected > 0 ? round($expected * ($tolerancePercent / 100), 2) : 0;
        $allowedDifference = max($percentAllowance, $toleranceAbsolute);
        $hasVariance = abs($difference) > $allowedDifference + 0.009;

        $this->fill([
            'subtotal' => round($subtotal, 2),
            'total' => $total,
            'balance_due' => $balance,
            'price_variance_amount' => $difference,
            'has_price_variance' => $hasVariance,
        ]);

        if ($balance <= 0 && $this->status !== 'paid') {
            $this->status = 'paid';
        }
    }
}
