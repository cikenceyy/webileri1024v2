<?php

namespace App\Modules\Settings\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Genel ayarlar formu için doğrulama kuralları.
 */
class GeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'company_locale' => ['required', 'string', 'max:5'],
            'company_timezone' => ['required', 'string', 'max:64', 'timezone:all'],
            'company_logo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
