<?php

namespace App\Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();

        return [
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('company_id', $companyId)],
            'receipt_no' => ['nullable', 'string', 'max:64', Rule::unique('receipts', 'receipt_no')->where('company_id', $companyId)],
            'receipt_date' => ['required', 'date'],
            'currency' => ['required', Rule::in(config('finance.supported_currencies'))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'bank_account_id' => ['nullable', Rule::exists('bank_accounts', 'id')->where('company_id', $companyId)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
