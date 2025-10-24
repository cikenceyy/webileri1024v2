<?php

namespace App\Consoles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class O2CFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string'],
            'customer_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ];
    }
}
