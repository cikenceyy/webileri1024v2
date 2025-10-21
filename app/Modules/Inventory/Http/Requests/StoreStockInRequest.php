<?php

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Inventory\Domain\Models\StockMovement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $reasonRules = Rule::in(StockMovement::REASONS);

        return [
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'qty' => ['required', 'numeric', 'gt:0'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'reason' => ['nullable', $reasonRules],
            'ref_type' => ['nullable', 'string', 'max:255'],
            'ref_id' => ['nullable', 'integer'],
            'note' => ['nullable', 'string'],
            'moved_at' => ['nullable', 'date'],
        ];
    }
}
