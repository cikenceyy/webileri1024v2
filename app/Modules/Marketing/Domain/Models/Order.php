<?php

namespace App\Modules\Marketing\Domain\Models;

use App\Core\Contracts\SettingsReader;
use App\Core\Domain\Sequencing\Sequencer;
use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Production\Domain\Models\WorkOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use BelongsToCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'contact_id',
        'order_no',
        'order_date',
        'due_date',
        'currency',
        'status',
        'total_amount',
        'subtotal',
        'discount_total',
        'tax_total',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CustomerContact::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public static function generateOrderNo(int $companyId): string
    {
        /** @var SettingsReader $settings */
        $settings = app(SettingsReader::class);
        /** @var Sequencer $sequencer */
        $sequencer = app(Sequencer::class);

        $config = $settings->get($companyId)->sequencing;
        $prefix = (string) ($config['order_prefix'] ?? 'SO');
        if ($prefix === '') {
            $prefix = 'SO';
        }

        $padding = max(3, min(8, (int) ($config['padding'] ?? 6)));
        $policy = in_array($config['reset_policy'] ?? 'yearly', ['yearly', 'never'], true)
            ? $config['reset_policy']
            : 'yearly';

        if (! config('features.sequencer.v2', true)) {
            $nextNumber = (int) (static::query()->where('company_id', $companyId)->max('id') ?? 0) + 1;

            return $prefix . str_pad((string) $nextNumber, $padding, '0', STR_PAD_LEFT);
        }

        return $sequencer->next($companyId, 'sales_order', $prefix, $padding, $policy);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($w) use ($term): void {
            $like = '%' . $term . '%';

            $w->where('order_no', 'like', $like)
                ->orWhereHas('customer', static function ($c) use ($like): void {
                    $c->where('name', 'like', $like)
                        ->orWhere('code', 'like', $like);
                });
        });
    }
}
