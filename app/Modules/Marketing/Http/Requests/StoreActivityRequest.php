<?php

namespace App\Modules\Marketing\Http\Requests;

use App\Modules\Marketing\Domain\Models\Activity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Activity::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['call', 'meeting', 'task'])],
            'due_at' => ['nullable', 'date'],
            'done_at' => ['nullable', 'date', 'after_or_equal:due_at'],
            'related_type' => ['nullable', 'string', 'max:255'],
            'related_id' => ['nullable', 'integer'],
            'assigned_to' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
