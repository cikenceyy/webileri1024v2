<?php

namespace App\Consoles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplenishFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'threshold' => ['nullable', 'numeric'],
            'product_id' => ['nullable', 'integer'],
        ];
    }
}
