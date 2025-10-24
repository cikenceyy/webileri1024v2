<?php

namespace App\Modules\Production\Http\Requests\Admin;

use App\Modules\Production\Domain\Models\Bom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BomStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Bom::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();

        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'code' => ['required', 'string', 'max:64'],
            'version' => ['nullable', 'integer', 'min:1'],
            'output_qty' => ['required', 'numeric', 'min:0.001'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.component_product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'items.*.component_variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'items.*.qty_per' => ['required', 'numeric', 'min:0.0001'],
            'items.*.wastage_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.default_warehouse_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'items.*.default_bin_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouse_bins', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'items.*.sort' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
