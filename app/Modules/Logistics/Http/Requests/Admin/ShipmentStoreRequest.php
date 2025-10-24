<?php

namespace App\Modules\Logistics\Http\Requests\Admin;

use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipmentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Shipment::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();

        return [
            'customer_id' => [
                'nullable',
                'integer',
                Rule::exists('customers', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'warehouse_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'packages_count' => ['nullable', 'integer', 'min:0'],
            'gross_weight' => ['nullable', 'numeric', 'min:0'],
            'net_weight' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'lines.*.variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'lines.*.qty' => ['required', 'numeric', 'min:0.001'],
            'lines.*.uom' => ['nullable', 'string', 'max:16'],
            'lines.*.notes' => ['nullable', 'string'],
        ];
    }
}
