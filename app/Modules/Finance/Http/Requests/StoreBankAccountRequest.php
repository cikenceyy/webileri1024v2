<?php

namespace App\Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('bank_accounts', 'name')->where('company_id', $companyId)],
            'account_no' => ['nullable', 'string', 'max:120'],
            'currency' => ['required', Rule::in(config('finance.supported_currencies'))],
            'is_default' => ['sometimes', 'boolean'],
            'status' => ['required', 'string', 'max:24'],
        ];
    }
}
