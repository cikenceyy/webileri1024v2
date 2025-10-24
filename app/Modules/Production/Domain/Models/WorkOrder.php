<?php

namespace App\Modules\Production\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'doc_no',
        'product_id',
        'variant_id',
        'bom_id',
        'target_qty',
        'uom',
        'status',
        'due_date',
        'started_at',
        'completed_at',
        'source_type',
        'source_id',
        'notes',
    ];

    protected $casts = [
        'target_qty' => 'float',
        'due_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(WorkOrderIssue::class)->orderBy('posted_at');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(WorkOrderReceipt::class)->orderBy('posted_at');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(WorkOrderOperation::class)->orderBy('sort');
    }
}
