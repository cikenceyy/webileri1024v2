<?php

namespace App\Modules\HR\Http\Requests\Admin;

use App\Modules\HR\Domain\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Department $department */
        $department = $this->route('department');

        return $department ? ($this->user()?->can('update', $department) ?? false) : false;
    }

    public function rules(): array
    {
        $department = $this->route('department');
        $departmentId = $department?->id ?? 0;
        $companyId = currentCompanyId();

        return [
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('departments', 'code')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->ignore($departmentId),
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
