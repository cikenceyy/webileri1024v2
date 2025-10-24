<?php

namespace App\Modules\HR\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\HR\Domain\Models\Department;
use App\Modules\HR\Domain\Models\Employee;
use App\Modules\HR\Domain\Models\EmploymentType;
use App\Modules\HR\Domain\Models\Title;
use App\Modules\HR\Http\Requests\Admin\EmployeeStoreRequest;
use App\Modules\HR\Http\Requests\Admin\EmployeeUpdateRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Employee::class);

        $companyId = currentCompanyId();
        $query = Employee::query()
            ->with(['department', 'title', 'employmentType'])
            ->where('company_id', $companyId)
            ->orderBy('name');

        $search = trim((string) $request->query('q'));
        $departmentId = (int) $request->query('department_id');
        $titleId = (int) $request->query('title_id');
        $employmentTypeId = (int) $request->query('employment_type_id');
        $status = $request->query('status');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($departmentId > 0) {
            $query->where('department_id', $departmentId);
        }

        if ($titleId > 0) {
            $query->where('title_id', $titleId);
        }

        if ($employmentTypeId > 0) {
            $query->where('employment_type_id', $employmentTypeId);
        }

        if ($status && in_array($status, ['active', 'inactive'], true)) {
            $query->where('is_active', $status === 'active');
        }

        /** @var LengthAwarePaginator $employees */
        $employees = $query->paginate()->withQueryString();

        return view('hr::admin.employees.index', [
            'employees' => $employees,
            'departments' => $this->departmentOptions(),
            'titles' => $this->titleOptions(),
            'employmentTypes' => $this->employmentTypeOptions(),
            'filters' => [
                'q' => $search,
                'department_id' => $departmentId,
                'title_id' => $titleId,
                'employment_type_id' => $employmentTypeId,
                'status' => $status,
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Employee::class);

        return view('hr::admin.employees.create', [
            'departments' => $this->departmentOptions(),
            'titles' => $this->titleOptions(),
            'employmentTypes' => $this->employmentTypeOptions(),
            'users' => $this->userOptions(),
        ]);
    }

    public function store(EmployeeStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = currentCompanyId();

        Employee::create($data);

        return redirect()
            ->route('admin.hr.employees.index')
            ->with('status', __('Personel kaydı oluşturuldu.'));
    }

    public function show(Employee $employee): View
    {
        $this->authorize('view', $employee);

        return view('hr::admin.employees.show', [
            'employee' => $employee->load(['department', 'title', 'employmentType', 'user']),
        ]);
    }

    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);

        return view('hr::admin.employees.edit', [
            'employee' => $employee,
            'departments' => $this->departmentOptions(),
            'titles' => $this->titleOptions(),
            'employmentTypes' => $this->employmentTypeOptions(),
            'users' => $this->userOptions(),
        ]);
    }

    public function update(EmployeeUpdateRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());

        return redirect()
            ->route('admin.hr.employees.show', $employee)
            ->with('status', __('Personel güncellendi.'));
    }

    public function archive(Employee $employee): RedirectResponse
    {
        $this->authorize('archive', $employee);

        $employee->update([
            'is_active' => false,
            'termination_date' => $employee->termination_date ?? now()->toDateString(),
        ]);

        return redirect()
            ->route('admin.hr.employees.index')
            ->with('status', __('Personel arşivlendi.'));
    }

    protected function departmentOptions()
    {
        return Department::query()->orderBy('name')->pluck('name', 'id');
    }

    protected function titleOptions()
    {
        return Title::query()->orderBy('name')->pluck('name', 'id');
    }

    protected function employmentTypeOptions()
    {
        return EmploymentType::query()->orderBy('name')->pluck('name', 'id');
    }

    protected function userOptions()
    {
        return User::query()
            ->where('company_id', currentCompanyId())
            ->orderBy('name')
            ->pluck('name', 'id');
    }
}
