<?php

namespace App\Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();
        $invoiceId = $this->route('invoice')?->id;

        return [
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('company_id', $companyId)],
            'order_id' => ['nullable', Rule::exists('orders', 'id')->where('company_id', $companyId)],
            'invoice_no' => ['nullable', 'string', 'max:64', Rule::unique('invoices', 'invoice_no')->where('company_id', $companyId)->ignore($invoiceId)],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'currency' => ['required', Rule::in(config('finance.supported_currencies'))],
            'status' => ['nullable', 'string', 'max:32'],
            'shipping_total' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id' => ['nullable', Rule::exists('invoice_lines', 'id')->where('company_id', $companyId)->where('invoice_id', $invoiceId)],
            'lines.*.product_id' => ['nullable', Rule::exists('products', 'id')->where('company_id', $companyId)],
            'lines.*.variant_id' => ['nullable', Rule::exists('product_variants', 'id')->where('company_id', $companyId)],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.qty' => ['required', 'numeric', 'min:0.0001'],
            'lines.*.unit' => ['nullable', 'string', 'max:16'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
