<?php

namespace App\Consoles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseoutPrintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selection' => ['required', 'array'],
            'selection.*.type' => ['required', 'string'],
            'selection.*.id' => ['required', 'integer'],
        ];
    }
}
