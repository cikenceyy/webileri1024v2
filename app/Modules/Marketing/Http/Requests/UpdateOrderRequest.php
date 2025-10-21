<?php

namespace App\Modules\Marketing\Http\Requests;

use App\Modules\Marketing\Domain\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Order $order */
        $order = $this->route('order');

        return $this->user()?->can('update', $order) ?? false;
    }

    public function rules(): array
    {
        /** @var Order $order */
        $order = $this->route('order');
        $companyId = currentCompanyId();

        return [
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'contact_id' => [
                'nullable',
                Rule::exists('customer_contacts', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'order_no' => [
                'required',
                'string',
                'max:32',
                Rule::unique('orders', 'order_no')
                    ->ignore($order?->id)
                    ->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'order_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'currency' => ['required', Rule::in(['TRY', 'USD', 'EUR'])],
            'status' => ['required', Rule::in(['draft', 'confirmed', 'shipped', 'cancelled'])],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.qty' => ['required', 'numeric', 'min:0.001'],
            'lines.*.unit' => ['nullable', 'string', 'max:16'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'lines.*.variant_id' => [
                'nullable',
                Rule::exists('product_variants', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
        ];
    }
}
