<?php

namespace App\Consoles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MTOActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'order_id' => ['nullable', 'integer'],
            'work_order_id' => ['nullable', 'integer'],
            'qty' => ['nullable', 'numeric', 'min:0'],
            'materials' => ['nullable', 'array'],
            'materials.*.product_id' => ['nullable', 'integer'],
            'materials.*.variant_id' => ['nullable', 'integer'],
            'materials.*.qty' => ['nullable', 'numeric', 'min:0'],
            'materials.*.unit' => ['nullable', 'string', 'max:20'],
            'materials.*.notes' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
