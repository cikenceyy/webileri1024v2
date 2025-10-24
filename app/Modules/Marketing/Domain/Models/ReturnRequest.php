<?php

namespace App\Modules\Marketing\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnRequest extends Model
{
    use BelongsToCompany;

    protected $table = 'returns';

    protected $fillable = [
        'company_id',
        'customer_id',
        'related_order_id',
        'status',
        'reason',
        'notes',
    ];

    public const STATUS_OPEN = 'open';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CLOSED = 'closed';

    public static function statuses(): array
    {
        return [self::STATUS_OPEN, self::STATUS_APPROVED, self::STATUS_CLOSED];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'related_order_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ReturnRequestLine::class, 'return_id');
    }

    public function scopeSearch(Builder $builder, ?string $term): Builder
    {
        if (! $term) {
            return $builder;
        }

        $like = '%' . trim($term) . '%';

        return $builder->where(function (Builder $query) use ($like): void {
            $query->where('reason', 'like', $like)
                ->orWhereHas('customer', static function (Builder $customerQuery) use ($like): void {
                    $customerQuery->where('name', 'like', $like);
                });
        });
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
