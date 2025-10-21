<?php

namespace App\Consoles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class O2CActionRequest extends FormRequest
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
            'shipment_id' => ['nullable', 'integer'],
            'invoice_id' => ['nullable', 'integer'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'bank_account_id' => ['nullable', 'integer'],
            'receipt_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
