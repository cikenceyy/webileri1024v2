<?php

namespace App\Consoles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReturnsActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['approve', 'close', 'create_receipt'])],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ];
    }
}
