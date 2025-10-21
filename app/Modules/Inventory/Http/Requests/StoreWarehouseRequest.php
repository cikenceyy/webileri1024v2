<?php

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->attributes->get('company_id') ?? (app()->bound('company') ? app('company')->id : null);

        return [
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('warehouses', 'code')->where(fn ($q) => $companyId ? $q->where('company_id', $companyId) : $q)->ignore($this->route('warehouse')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'is_default' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
