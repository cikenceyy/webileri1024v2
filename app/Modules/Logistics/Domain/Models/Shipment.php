<?php

namespace App\Modules\Logistics\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use BelongsToCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'shipment_no',
        'ship_date',
        'status',
        'customer_id',
        'order_id',
        'carrier',
        'carrier_id',
        'tracking_no',
        'package_count',
        'weight_kg',
        'volume_dm3',
        'warehouse_id',
        'shipping_cost',
        'picking_started_at',
        'packed_at',
        'shipped_at',
        'delivered_at',
        'returned_at',
        'notes',
    ];

    protected $casts = [
        'ship_date' => 'date',
        'weight_kg' => 'decimal:3',
        'volume_dm3' => 'decimal:3',
        'shipping_cost' => 'decimal:2',
        'picking_started_at' => 'datetime',
        'packed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Core\Support\Models\Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public static function generateNo(int $companyId): string
    {
        $prefix = 'SHP-' . now()->format('Ymd') . '-';

        do {
            $sequence = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            $candidate = $prefix . $sequence;
        } while (static::query()
            ->where('company_id', $companyId)
            ->where('shipment_no', $candidate)
            ->exists());

        return $candidate;
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($builder) use ($like) {
            $builder->where('shipment_no', 'like', $like)
                ->orWhere('tracking_no', 'like', $like);
        });
    }
}
