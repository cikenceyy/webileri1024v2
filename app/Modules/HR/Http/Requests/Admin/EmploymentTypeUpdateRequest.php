<?php

namespace App\Modules\HR\Http\Requests\Admin;

use App\Modules\HR\Domain\Models\EmploymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmploymentTypeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var EmploymentType $employmentType */
        $employmentType = $this->route('employment_type');

        return $employmentType ? ($this->user()?->can('update', $employmentType) ?? false) : false;
    }

    public function rules(): array
    {
        $employmentType = $this->route('employment_type');
        $employmentTypeId = $employmentType?->id ?? 0;
        $companyId = currentCompanyId();

        return [
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('employment_types', 'code')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->ignore($employmentTypeId),
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
