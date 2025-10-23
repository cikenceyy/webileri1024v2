<?php

namespace App\Modules\Finance\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use BelongsToCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'order_id',
        'invoice_no',
        'issue_date',
        'due_date',
        'currency',
        'status',
        'collection_lane',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'balance_due',
        'shipping_total',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'shipping_total' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('sort_order');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }

    public static function generateInvoiceNo(int $companyId): string
    {
        return next_number('INV', [
            'prefix' => 'INV',
        ], $companyId);
    }

    public function refreshTotals(): void
    {
        $subtotal = 0;
        $discount = 0;
        $tax = 0;
        $grand = 0;

        foreach ($this->lines as $line) {
            $lineBase = $line->qty * $line->unit_price;
            $lineDiscount = $lineBase * ($line->discount_rate / 100);
            $lineNet = $lineBase - $lineDiscount;
            $lineTax = $lineNet * ($line->tax_rate / 100);

            $subtotal += $lineBase;
            $discount += $lineDiscount;
            $tax += $lineTax;
            $grand += $lineNet + $lineTax;
        }

        $allocated = $this->allocations()->sum('amount');

        $grand += (float) $this->shipping_total;

        $this->fill([
            'subtotal' => round($subtotal, 2),
            'discount_total' => round($discount, 2),
            'tax_total' => round($tax, 2),
            'grand_total' => round($grand, 2),
            'balance_due' => round($grand - $allocated, 2),
        ]);
    }
}
