<?php

namespace App\Consoles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class O2CBulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in([
                'orders_to_shipments',
                'shipments_pick',
                'shipments_pack',
                'shipments_ship',
                'orders_to_invoices',
                'invoices_apply_receipt',
            ])],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
            'warehouse_id' => ['nullable', 'integer'],
        ];
    }
}
