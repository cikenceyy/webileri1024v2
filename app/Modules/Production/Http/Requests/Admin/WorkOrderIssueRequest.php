<?php

namespace App\Modules\Production\Http\Requests\Admin;

use App\Modules\Production\Domain\Models\WorkOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkOrderIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var WorkOrder|null $workOrder */
        $workOrder = $this->route('workOrder');

        return $workOrder ? ($this->user()?->can('issue', $workOrder) ?? false) : false;
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();

        return [
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.component_product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'lines.*.component_variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'lines.*.warehouse_id' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'lines.*.bin_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouse_bins', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'lines.*.qty' => ['required', 'numeric', 'min:0.0001'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $lines = $this->input('lines', []);
        if (is_array($lines)) {
            foreach ($lines as $index => $line) {
                if (isset($line['bin_id']) && $line['bin_id'] === '') {
                    $lines[$index]['bin_id'] = null;
                }
            }
            $this->merge(['lines' => $lines]);
        }
    }
}
