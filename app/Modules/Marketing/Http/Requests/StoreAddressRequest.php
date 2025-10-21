<?php

namespace App\Modules\Marketing\Http\Requests;

use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\CustomerAddress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->route('customer');
        if ($customer instanceof Customer) {
            return $this->user()?->can('update', $customer) ?? false;
        }

        $address = $this->route('address');
        if ($address instanceof CustomerAddress) {
            return $this->user()?->can('update', $address->customer) ?? false;
        }

        return false;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'type' => [$required, Rule::in(['billing', 'shipping'])],
            'line1' => [$required, 'string', 'max:255'],
            'line2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'line3' => ['sometimes', 'nullable', 'string', 'max:255'],
            'line4' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:120'],
            'state' => ['sometimes', 'nullable', 'string', 'max:120'],
            'country' => ['sometimes', 'nullable', 'string', 'max:2'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:16'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
