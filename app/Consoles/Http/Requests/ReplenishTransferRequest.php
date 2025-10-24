<?php

namespace App\Consoles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplenishTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_warehouse_id' => ['required', 'integer'],
            'to_warehouse_id' => ['required', 'integer'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'integer'],
            'lines.*.qty' => ['required', 'numeric', 'min:0.001'],
            'lines.*.note' => ['nullable', 'string'],
        ];
    }
}
