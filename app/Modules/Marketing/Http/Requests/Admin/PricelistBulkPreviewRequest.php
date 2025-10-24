<?php

namespace App\Modules\Marketing\Http\Requests\Admin;

use App\Modules\Inventory\Domain\Models\PriceList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PricelistBulkPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var PriceList $pricelist */
        $pricelist = $this->route('pricelist');

        return $pricelist && ($this->user()?->can('bulkUpdate', $pricelist) ?? false);
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();

        return [
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'search' => ['nullable', 'string', 'max:120'],
            'operation.type' => ['required', Rule::in(['percent', 'fixed'])],
            'operation.mode' => ['required', Rule::in(['increase', 'decrease'])],
            'operation.value' => ['required', 'numeric'],
            'operation.round' => ['nullable', 'numeric', 'in:0,0.05,0.1'],
        ];
    }
}
