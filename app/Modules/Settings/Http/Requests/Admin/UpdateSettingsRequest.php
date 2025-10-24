<?php

namespace App\Modules\Settings\Http\Requests\Admin;

use App\Modules\Settings\Domain\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', Setting::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->attributes->get('company_id');

        if (! $companyId && app()->bound('company')) {
            $companyId = app('company')->id ?? null;
        }

        $warehouseRule = Rule::exists('warehouses', 'id');
        $priceListRule = Rule::exists('price_lists', 'id');

        if ($companyId) {
            $warehouseRule = $warehouseRule->where(fn ($query) => $query->where('company_id', $companyId));
            $priceListRule = $priceListRule->where(fn ($query) => $query->where('company_id', $companyId));
        }

        return [
            'money.base_currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'money.allowed_currencies' => ['required', 'array', 'min:1'],
            'money.allowed_currencies.*' => ['string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'tax.default_vat_rate' => ['required', 'numeric', 'min:0', 'max:50'],
            'tax.withholding_enabled' => ['required', 'boolean'],
            'sequencing.invoice_prefix' => ['required', 'string', 'regex:/^[A-Z0-9\-_\/]+$/'],
            'sequencing.order_prefix' => ['required', 'string', 'regex:/^[A-Z0-9\-_\/]+$/'],
            'sequencing.shipment_prefix' => ['required', 'string', 'regex:/^[A-Z0-9\-_\/]+$/'],
            'sequencing.grn_prefix' => ['required', 'string', 'regex:/^[A-Z0-9\-_\/]+$/'],
            'sequencing.work_order_prefix' => ['required', 'string', 'regex:/^[A-Z0-9\-_\/]+$/'],
            'sequencing.padding' => ['required', 'integer', 'min:3', 'max:8'],
            'sequencing.reset_policy' => ['required', Rule::in(['never', 'yearly'])],
            'defaults.payment_terms_days' => ['required', 'integer', 'min:0', 'max:180'],
            'defaults.warehouse_id' => ['nullable', 'integer', $warehouseRule],
            'defaults.price_list_id' => ['nullable', 'integer', $priceListRule],
            'defaults.tax_inclusive' => ['required', 'boolean'],
            'defaults.production_issue_warehouse_id' => ['nullable', 'integer', $warehouseRule],
            'defaults.production_receipt_warehouse_id' => ['nullable', 'integer', $warehouseRule],
            'defaults.shipment_warehouse_id' => ['nullable', 'integer', $warehouseRule],
            'defaults.receipt_warehouse_id' => ['nullable', 'integer', $warehouseRule],
            'documents.invoice_print_template' => ['nullable', 'string', 'max:191'],
            'documents.shipment_note_template' => ['nullable', 'string', 'max:191'],
            'documents.grn_note_template' => ['nullable', 'string', 'max:191'],
            'general.company_locale' => ['required', 'string', 'max:10', 'regex:/^[a-z]{2}[_-][A-Z]{2}$/'],
            'general.timezone' => ['required', 'string', 'timezone:all'],
            'general.decimal_precision' => ['required', 'integer', Rule::in([2, 3])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->attributes->get('company_id') && app()->bound('company')) {
            $company = app('company');
            if ($company && isset($company->id)) {
                $this->attributes->set('company_id', $company->id);
            }
        }

        $money = $this->input('money', []);
        $allowedCurrencies = $money['allowed_currencies'] ?? [];

        if (is_string($allowedCurrencies)) {
            $allowedCurrencies = preg_split('/[\s,;]+/', $allowedCurrencies, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $allowedCurrencies = array_values(array_filter(array_map(static function ($code) {
            return strtoupper(trim((string) $code));
        }, Arr::wrap($allowedCurrencies))));

        $money['allowed_currencies'] = $allowedCurrencies;

        if (isset($money['base_currency'])) {
            $money['base_currency'] = strtoupper((string) $money['base_currency']);
        }

        $tax = $this->input('tax', []);
        $tax['withholding_enabled'] = filter_var($tax['withholding_enabled'] ?? false, FILTER_VALIDATE_BOOL);

        $defaults = $this->input('defaults', []);
        $defaults['tax_inclusive'] = filter_var($defaults['tax_inclusive'] ?? false, FILTER_VALIDATE_BOOL);
        foreach (['warehouse_id', 'price_list_id', 'production_issue_warehouse_id', 'production_receipt_warehouse_id', 'shipment_warehouse_id', 'receipt_warehouse_id'] as $key) {
            if (array_key_exists($key, $defaults) && $defaults[$key] === '') {
                $defaults[$key] = null;
            }
        }

        $documents = $this->input('documents', []);
        foreach ($documents as $key => $value) {
            $documents[$key] = is_string($value) ? trim($value) : $value;
        }

        $general = $this->input('general', []);
        if (isset($general['company_locale'])) {
            $general['company_locale'] = str_replace('-', '_', (string) $general['company_locale']);
            $general['company_locale'] = strtolower(substr($general['company_locale'], 0, 2)) . '_' . strtoupper(substr($general['company_locale'], -2));
        }

        $this->merge([
            'money' => $money,
            'tax' => $tax,
            'defaults' => $defaults,
            'documents' => $documents,
            'general' => $general,
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator): void {
            $base = $this->input('money.base_currency');
            $allowed = $this->input('money.allowed_currencies', []);

            if ($base && is_array($allowed) && ! in_array($base, $allowed, true)) {
                $validator->errors()->add('money.allowed_currencies', __('Temel para birimi izin verilenler arasında olmalıdır.'));
            }
        });
    }
}
