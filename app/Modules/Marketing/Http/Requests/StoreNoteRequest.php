<?php

namespace App\Modules\Marketing\Http\Requests;

use App\Modules\Marketing\Domain\Models\Note;
use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Note::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'related_type' => ['required', 'string', 'max:255'],
            'related_id' => ['required', 'integer'],
            'body' => ['required', 'string'],
        ];
    }
}
