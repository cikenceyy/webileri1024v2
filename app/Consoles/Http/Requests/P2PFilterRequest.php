<?php

namespace App\Consoles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class P2PFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string'],
        ];
    }
}
