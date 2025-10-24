<?php

namespace App\Modules\HR\Http\Requests\Admin;

use App\Modules\HR\Domain\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Employee::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();

        return [
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('employees', 'code')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:32'],
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'title_id' => [
                'nullable',
                'integer',
                Rule::exists('titles', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'employment_type_id' => [
                'nullable',
                'integer',
                Rule::exists('employment_types', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'hire_date' => ['nullable', 'date'],
            'termination_date' => ['nullable', 'date', 'after_or_equal:hire_date'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
