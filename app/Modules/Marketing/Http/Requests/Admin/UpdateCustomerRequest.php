<?php

namespace App\Modules\Marketing\Http\Requests\Admin;

use App\Modules\Marketing\Domain\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Customer $customer */
        $customer = $this->route('customer');

        return $customer && ($this->user()?->can('update', $customer) ?? false);
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();

        return [
            'name' => ['required', 'string', 'max:150'],
            'tax_no' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:32'],
            'payment_terms_days' => ['required', 'integer', 'min:0', 'max:180'],
            'default_price_list_id' => [
                'nullable',
                'integer',
                Rule::exists('price_lists', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'billing_address' => ['nullable', 'array'],
            'billing_address.line1' => ['nullable', 'string', 'max:120'],
            'billing_address.city' => ['nullable', 'string', 'max:60'],
            'billing_address.country' => ['nullable', 'string', 'max:2'],
            'shipping_address' => ['nullable', 'array'],
            'shipping_address.line1' => ['nullable', 'string', 'max:120'],
            'shipping_address.city' => ['nullable', 'string', 'max:60'],
            'shipping_address.country' => ['nullable', 'string', 'max:2'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
