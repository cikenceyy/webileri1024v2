<?php

namespace App\Modules\Inventory\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantValue extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'product_variant_id',
        'attribute_id',
        'value_id',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(VariantAttribute::class, 'attribute_id');
    }

    public function value(): BelongsTo
    {
        return $this->belongsTo(VariantAttributeValue::class, 'value_id');
    }
}
