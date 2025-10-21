<?php

namespace App\Modules\Marketing\Http\Requests;

use App\Modules\Marketing\Domain\Models\Quote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Quote $quote */
        $quote = $this->route('quote');

        return $this->user()?->can('update', $quote) ?? false;
    }

    public function rules(): array
    {
        /** @var Quote $quote */
        $quote = $this->route('quote');
        $companyId = currentCompanyId();

        return [
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'contact_id' => [
                'nullable',
                Rule::exists('customer_contacts', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'quote_no' => [
                'required',
                'string',
                'max:32',
                Rule::unique('quotes', 'quote_no')
                    ->ignore($quote?->id)
                    ->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'date' => ['required', 'date'],
            'currency' => ['required', Rule::in(['TRY', 'USD', 'EUR'])],
            'status' => ['required', Rule::in(['draft', 'sent', 'accepted', 'rejected', 'cancelled'])],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.qty' => ['required', 'numeric', 'min:0.001'],
            'lines.*.unit' => ['nullable', 'string', 'max:16'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'lines.*.variant_id' => [
                'nullable',
                Rule::exists('product_variants', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
        ];
    }
}
