<?php

namespace App\Consoles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReturnsFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string'],
        ];
    }
}
