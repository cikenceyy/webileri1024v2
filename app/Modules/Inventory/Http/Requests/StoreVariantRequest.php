<?php

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->attributes->get('company_id') ?? (app()->bound('company') ? app('company')->id : null);

        return [
            'sku' => [
                'required',
                'string',
                'max:64',
                Rule::unique('product_variants', 'sku')->where(fn ($q) => $companyId ? $q->where('company_id', $companyId) : $q)->ignore($this->route('variant')),
            ],
            'barcode' => ['nullable', 'string', 'max:64'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
