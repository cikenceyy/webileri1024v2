<?php

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->attributes->get('company_id') ?? (app()->bound('company') ? app('company')->id : null);

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('product_categories', 'id')->where(fn ($q) => $companyId ? $q->where('company_id', $companyId) : $q),
            ],
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('product_categories', 'code')->where(fn ($q) => $companyId ? $q->where('company_id', $companyId) : $q)->ignore($this->route('category')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_categories', 'slug')
                    ->where(fn ($q) => $companyId ? $q->where('company_id', $companyId) : $q)
                    ->where(fn ($q) => $this->filled('parent_id') ? $q->where('parent_id', $this->input('parent_id')) : $q->whereNull('parent_id'))
                    ->ignore($this->route('category')),
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
