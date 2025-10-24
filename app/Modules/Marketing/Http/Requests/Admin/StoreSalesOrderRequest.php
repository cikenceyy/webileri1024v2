<?php

namespace App\Modules\Marketing\Http\Requests\Admin;

use App\Modules\Marketing\Domain\Models\SalesOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SalesOrder::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();

        return [
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'price_list_id' => [
                'nullable',
                'integer',
                Rule::exists('price_lists', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'currency' => ['required', 'string', 'size:3'],
            'tax_inclusive' => ['required', 'boolean'],
            'payment_terms_days' => ['required', 'integer', 'min:0', 'max:180'],
            'due_date' => ['nullable', 'date'],
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
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.discount_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'lines.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tax_inclusive' => $this->boolean('tax_inclusive'),
        ]);
    }
}
