<?php

namespace App\Modules\_template\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Örnek form request iskeleti.
 * Kuralları ve yetkilendirmeyi modül ihtiyacına göre güncelleyin.
 */
class ExampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage example module') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
