<?php

namespace App\Modules\Marketing\Http\Requests\Admin;

use App\Modules\Marketing\Domain\Models\ReturnRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ReturnRequest::class) ?? false;
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
            'related_order_id' => [
                'nullable',
                'integer',
                Rule::exists('sales_orders', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'reason' => ['nullable', 'string', 'max:120'],
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
            'lines.*.reason_code' => ['nullable', 'string', 'max:64'],
            'lines.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
