<?php

namespace App\Consoles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MTOBulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in([
                'plan',
                'issue',
                'complete',
                'close',
            ])],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
            'qty' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
