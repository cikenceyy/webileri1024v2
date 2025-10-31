<?php

namespace App\Modules\HR\Http\Controllers\Admin;

use App\Core\Support\TableKit\Filters;
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

        $search = trim((string) $request->query('q', ''));
        $departmentId = Filters::scalar($request, 'department_id');
        $titleId = Filters::scalar($request, 'title_id');
        $employmentTypeId = Filters::scalar($request, 'employment_type_id');
        $statusFilters = Filters::multi($request, 'status');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($departmentId) {
            $query->where('department_id', (int) $departmentId);
        }

        if ($titleId) {
            $query->where('title_id', (int) $titleId);
        }

        if ($employmentTypeId) {
            $query->where('employment_type_id', (int) $employmentTypeId);
        }

        $normalizedStatuses = collect($statusFilters)
            ->filter(fn (string $value) => in_array($value, ['active', 'inactive'], true))
            ->values();

        if ($normalizedStatuses->count() === 1) {
            $query->where('is_active', $normalizedStatuses->first() === 'active');
        }

        $perPage = (int) $request->integer('perPage', 25);
        $perPage = max(10, min(100, $perPage));

        /** @var LengthAwarePaginator $employees */
        $employees = $query->paginate($perPage)->withQueryString();

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
                'status' => $normalizedStatuses->all(),
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
