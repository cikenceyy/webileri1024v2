<?php

namespace App\Consoles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QualityActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'result' => ['required', Rule::in(['pass', 'fail'])],
            'notes' => ['nullable', 'string'],
            'selection' => ['required', 'array', 'min:1'],
            'selection.*.subject_type' => ['required', 'string'],
            'selection.*.subject_id' => ['required', 'integer'],
            'selection.*.direction' => ['required', Rule::in(['incoming', 'outgoing'])],
        ];
    }
}
