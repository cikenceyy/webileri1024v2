<?php

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseBinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->attributes->has('company_id') && auth()->check()) {
            $this->attributes->set('company_id', auth()->user()->company_id);
        }
    }

    public function rules(): array
    {
        $warehouse = $this->route('warehouse');
        $companyId = $warehouse ? $warehouse->company_id : (app()->bound('company') ? app('company')->id : null);

        return [
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('warehouse_bins', 'code')
                    ->where(fn ($q) => $companyId ? $q->where('company_id', $companyId) : $q)
                    ->where(fn ($q) => $warehouse ? $q->where('warehouse_id', $warehouse->id) : $q)
                    ->ignore($this->route('bin')),
            ],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
