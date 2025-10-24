<?php

namespace App\Modules\Production\Http\Requests\Admin;

use App\Modules\Production\Domain\Models\WorkOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkOrderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', WorkOrder::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();

        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'bom_id' => [
                'required',
                'integer',
                Rule::exists('boms', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'target_qty' => ['required', 'numeric', 'min:0.001'],
            'uom' => ['nullable', 'string', 'max:16'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'source_type' => ['nullable', 'string', 'max:120'],
            'source_id' => ['nullable', 'integer'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'target_qty' => $this->input('target_qty'),
        ]);
    }
}
