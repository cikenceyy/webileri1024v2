<?php

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Inventory\Domain\Models\StockMovement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'qty' => ['required', 'numeric', 'gt:0'],
            'reason' => ['nullable', Rule::in(StockMovement::REASONS)],
            'ref_type' => ['nullable', 'string', 'max:255'],
            'ref_id' => ['nullable', 'integer'],
            'note' => ['nullable', 'string'],
            'moved_at' => ['nullable', 'date'],
        ];
    }
}
