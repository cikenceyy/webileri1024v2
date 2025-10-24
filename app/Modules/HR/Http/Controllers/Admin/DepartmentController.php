<?php

namespace App\Modules\HR\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\HR\Domain\Models\Department;
use App\Modules\HR\Http\Requests\Admin\DepartmentStoreRequest;
use App\Modules\HR\Http\Requests\Admin\DepartmentUpdateRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Department::class);

        $query = Department::query()->where('company_id', currentCompanyId())->orderBy('name');
        $search = trim((string) $request->query('q'));
        $status = $request->query('status');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($status && in_array($status, ['active', 'inactive'], true)) {
            $query->where('is_active', $status === 'active');
        }

        /** @var LengthAwarePaginator $departments */
        $departments = $query->paginate()->withQueryString();

        return view('hr::admin.settings.departments.index', [
            'departments' => $departments,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Department::class);

        return view('hr::admin.settings.departments.create');
    }

    public function store(DepartmentStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = currentCompanyId();

        Department::create($data);

        return redirect()
            ->route('admin.hr.settings.departments.index')
            ->with('status', __('Departman oluşturuldu.'));
    }

    public function edit(Department $department): View
    {
        $this->authorize('update', $department);

        return view('hr::admin.settings.departments.edit', [
            'department' => $department,
        ]);
    }

    public function update(DepartmentUpdateRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated());

        return redirect()
            ->route('admin.hr.settings.departments.index')
            ->with('status', __('Departman güncellendi.'));
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete', $department);

        $department->delete();

        return redirect()
            ->route('admin.hr.settings.departments.index')
            ->with('status', __('Departman silindi.'));
    }
}
