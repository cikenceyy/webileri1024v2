<?php

namespace App\Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'theme_color' => ['nullable', 'string', 'max:16'],
            'logo_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('theme_color')) {
            $this->merge([
                'theme_color' => trim((string) $this->input('theme_color')),
            ]);
        }
    }
}
