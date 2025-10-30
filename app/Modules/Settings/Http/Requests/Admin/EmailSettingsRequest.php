<?php

namespace App\Modules\Settings\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * E-posta merkezi formu için doğrulama kuralları.
 */
class EmailSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'email_outbound_x' => ['nullable', 'email', 'max:255'],
            'email_outbound_y' => ['nullable', 'email', 'max:255'],
            'email_policy_deliver_to' => ['required', 'in:both,x_only,y_only'],
            'email_policy_from' => ['required', 'in:system,x,y'],
            'email_policy_reply_to' => ['required', 'in:x,y,none'],
            'email_brand_name' => ['nullable', 'string', 'max:255'],
            'email_brand_address' => ['nullable', 'email', 'max:255'],
        ];
    }
}
