<?php

namespace App\Modules\Marketing\Http\Requests;

use App\Modules\Marketing\Domain\Models\Opportunity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Opportunity::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(['TRY', 'USD', 'EUR'])],
            'stage' => ['required', Rule::in(['new', 'qualified', 'proposal', 'won', 'lost'])],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'close_date' => ['nullable', 'date'],
            'owner_id' => ['nullable', 'integer'],
        ];
    }
}
