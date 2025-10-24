<?php

namespace App\Modules\Finance\Http\Requests\Admin;

use App\Modules\Finance\Domain\Models\CashbookEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CashbookStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CashbookEntry::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'direction' => ['required', Rule::in(CashbookEntry::directions())],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'occurred_at' => ['required', 'date'],
            'account' => ['required', 'string', 'max:64'],
            'reference_type' => ['nullable', 'string', 'max:64'],
            'reference_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
