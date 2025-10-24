<?php

namespace App\Modules\HR\Http\Requests\Admin;

use App\Modules\HR\Domain\Models\Title;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TitleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Title::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();

        return [
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('titles', 'code')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'name' => ['required', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
