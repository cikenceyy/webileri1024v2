<?php

namespace App\Modules\Settings\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modül ayarları için doğrulama kurallarını barındırır.
 */
class ModuleSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'drive_enable_versioning' => ['required', 'boolean'],
            'inventory_low_stock_threshold' => ['required', 'integer', 'min:0'],
            'finance_default_currency' => ['required', 'string', 'size:3'],
            'cms_feature_flags' => ['nullable', 'json'],
        ];
    }
}
