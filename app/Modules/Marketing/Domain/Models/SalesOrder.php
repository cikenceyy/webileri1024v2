<?php

namespace App\Modules\Marketing\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Inventory\Domain\Models\PriceList;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'customer_id',
        'price_list_id',
        'doc_no',
        'status',
        'currency',
        'tax_inclusive',
        'payment_terms_days',
        'due_date',
        'ordered_at',
        'notes',
        'confirmed_at',
        'fulfilled_at',
    ];

    protected $casts = [
        'tax_inclusive' => 'bool',
        'payment_terms_days' => 'int',
        'due_date' => 'date',
        'ordered_at' => 'date',
        'confirmed_at' => 'datetime',
        'fulfilled_at' => 'datetime',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_FULFILLED = 'fulfilled';
    public const STATUS_CANCELLED = 'cancelled';

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_CONFIRMED,
            self::STATUS_FULFILLED,
            self::STATUS_CANCELLED,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SalesOrderLine::class, 'order_id');
    }

    public function scopeSearch(Builder $builder, ?string $term): Builder
    {
        if (! $term) {
            return $builder;
        }

        $like = '%' . trim($term) . '%';

        return $builder->where(function (Builder $query) use ($like): void {
            $query->where('doc_no', 'like', $like)
                ->orWhereHas('customer', function (Builder $customerQuery) use ($like): void {
                    $customerQuery->where('name', 'like', $like);
                });
        });
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [self::STATUS_FULFILLED, self::STATUS_CANCELLED], true);
    }
}
