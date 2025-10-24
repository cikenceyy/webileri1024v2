<?php

namespace App\Modules\HR\Http\Requests\Admin;

use App\Modules\HR\Domain\Models\Title;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TitleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Title $title */
        $title = $this->route('title');

        return $title ? ($this->user()?->can('update', $title) ?? false) : false;
    }

    public function rules(): array
    {
        $title = $this->route('title');
        $titleId = $title?->id ?? 0;
        $companyId = currentCompanyId();

        return [
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('titles', 'code')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->ignore($titleId),
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
