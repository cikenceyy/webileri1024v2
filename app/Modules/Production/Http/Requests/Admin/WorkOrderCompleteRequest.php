<?php

namespace App\Modules\Production\Http\Requests\Admin;

use App\Modules\Production\Domain\Models\WorkOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkOrderCompleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var WorkOrder|null $workOrder */
        $workOrder = $this->route('workOrder');

        return $workOrder ? ($this->user()?->can('complete', $workOrder) ?? false) : false;
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();

        return [
            'qty' => ['required', 'numeric', 'min:0.0001'],
            'warehouse_id' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'bin_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouse_bins', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('bin_id') === '') {
            $this->merge(['bin_id' => null]);
        }
    }
}
