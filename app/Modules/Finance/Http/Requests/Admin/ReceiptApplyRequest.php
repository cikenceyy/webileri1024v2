<?php

namespace App\Modules\Finance\Http\Requests\Admin;

use App\Modules\Finance\Domain\Models\Receipt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceiptApplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Receipt|null $receipt */
        $receipt = $this->route('receipt');

        return $receipt ? ($this->user()?->can('apply', $receipt) ?? false) : false;
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();

        return [
            'applications' => ['required', 'array'],
            'applications.*.invoice_id' => [
                'required',
                Rule::exists('invoices', 'id')->where('company_id', $companyId),
            ],
            'applications.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
