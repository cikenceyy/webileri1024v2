<?php

namespace App\Modules\Marketing\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequestLine extends Model
{
    use BelongsToCompany;

    protected $table = 'return_lines';

    protected $fillable = [
        'company_id',
        'return_id',
        'product_id',
        'variant_id',
        'qty',
        'reason_code',
        'notes',
    ];

    protected $casts = [
        'qty' => 'float',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ReturnRequest::class, 'return_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
