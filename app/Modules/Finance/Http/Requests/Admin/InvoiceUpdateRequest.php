<?php

namespace App\Modules\Finance\Http\Requests\Admin;

use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Marketing\Domain\Models\SalesOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Invoice|null $invoice */
        $invoice = $this->route('invoice');

        return $invoice ? ($this->user()?->can('update', $invoice) ?? false) : false;
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();

        return [
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where('company_id', $companyId),
            ],
            'order_id' => [
                'nullable',
                Rule::exists('sales_orders', 'id')->where('company_id', $companyId)->where('status', SalesOrder::STATUS_CONFIRMED),
            ],
            'currency' => ['required', 'string', 'size:3', Rule::in($this->allowedCurrencies())],
            'tax_inclusive' => ['required', 'boolean'],
            'payment_terms_days' => ['required', 'integer', 'min:0', 'max:180'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id' => ['nullable', 'integer'],
            'lines.*.description' => ['required', 'string', 'max:240'],
            'lines.*.product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where('company_id', $companyId),
            ],
            'lines.*.variant_id' => [
                'nullable',
                Rule::exists('product_variants', 'id')->where('company_id', $companyId),
            ],
            'lines.*.qty' => ['required', 'numeric', 'min:0.001'],
            'lines.*.uom' => ['nullable', 'string', 'max:16'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.discount_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:50'],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function allowedCurrencies(): array
    {
        $settings = app(\App\Core\Contracts\SettingsReader::class)->get(currentCompanyId());

        return $settings->money['allowed_currencies'];
    }
}
