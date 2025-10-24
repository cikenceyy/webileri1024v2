<?php

namespace App\Modules\Finance\Http\Requests\Admin;

use App\Modules\Finance\Domain\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Invoice|null $invoice */
        $invoice = $this->route('invoice');

        return $invoice ? ($this->user()?->can('issue', $invoice) ?? false) : false;
    }

    public function rules(): array
    {
        return [
            'issued_at' => ['nullable', 'date'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:180'],
        ];
    }
}
