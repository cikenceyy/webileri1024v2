<?php

namespace App\Modules\Procurement\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Inventory\Domain\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GrnLine extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'grn_id',
        'po_line_id',
        'product_id',
        'qty_received',
        'uuid',
    ];

    protected $casts = [
        'qty_received' => 'decimal:3',
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(Grn::class, 'grn_id');
    }

    public function poLine(): BelongsTo
    {
        return $this->belongsTo(PoLine::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $line): void {
            if (! $line->uuid) {
                $line->uuid = (string) Str::uuid();
            }
        });
    }
}
