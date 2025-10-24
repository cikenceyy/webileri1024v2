<?php

namespace App\Consoles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class P2PBulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in([
                'approve_pos',
                'po_to_grn',
                'receive_grn',
                'reconcile_grn',
            ])],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
            'warehouse_id' => ['nullable', 'integer'],
            'reason' => ['nullable', 'string', 'max:120'],
        ];
    }
}
