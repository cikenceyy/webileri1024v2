<?php

namespace App\Modules\Finance\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\SalesOrder;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'customer_id',
        'order_id',
        'doc_no',
        'status',
        'currency',
        'tax_inclusive',
        'subtotal',
        'tax_total',
        'grand_total',
        'paid_amount',
        'payment_terms_days',
        'due_date',
        'issued_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'tax_inclusive' => 'bool',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'issued_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'due_date' => 'date',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_PARTIALLY_PAID = 'partially_paid';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_ISSUED,
            self::STATUS_PARTIALLY_PAID,
            self::STATUS_PAID,
            self::STATUS_CANCELLED,
        ];
    }

    public function scopeOpen(Builder $builder): Builder
    {
        return $builder->whereIn('status', [self::STATUS_ISSUED, self::STATUS_PARTIALLY_PAID]);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'order_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('sort');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(ReceiptApplication::class);
    }

    public function markIssued(string $docNo, \DateTimeInterface $issuedAt, int $terms): void
    {
        $issued = CarbonImmutable::instance($issuedAt);
        $dueDate = $terms > 0 ? $issued->addDays($terms) : $issued;

        $this->forceFill([
            'doc_no' => $docNo,
            'status' => self::STATUS_ISSUED,
            'issued_at' => $issued,
            'payment_terms_days' => $terms,
            'due_date' => $dueDate,
        ])->save();
    }

    public function syncPaymentStatus(): void
    {
        $balance = (float) $this->grand_total - (float) $this->paid_amount;

        if ($balance <= 0.0001) {
            $this->status = self::STATUS_PAID;
        } elseif ($this->paid_amount > 0) {
            $this->status = self::STATUS_PARTIALLY_PAID;
        } elseif ($this->status === self::STATUS_PAID || $this->status === self::STATUS_PARTIALLY_PAID) {
            $this->status = self::STATUS_ISSUED;
        }

        $this->save();
    }

    public function applyPayment(float $amount): void
    {
        $this->paid_amount = round(((float) $this->paid_amount) + $amount, 2);
        $this->syncPaymentStatus();
    }

    public function revertPayment(float $amount): void
    {
        $this->paid_amount = round(max(0, ((float) $this->paid_amount) - $amount), 2);
        $this->syncPaymentStatus();
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED || $this->status === self::STATUS_PARTIALLY_PAID;
    }

    public function isSettled(): bool
    {
        return in_array($this->status, [self::STATUS_PAID, self::STATUS_CANCELLED], true);
    }

    public function getBalanceDueAttribute(mixed $value): float
    {
        if ($value !== null) {
            return (float) $value;
        }

        return round((float) $this->grand_total - (float) $this->paid_amount, 2);
    }

    public function getBalanceAttribute(mixed $value): float
    {
        if ($value !== null) {
            return (float) $value;
        }

        return $this->getBalanceDueAttribute(null);
    }
}
