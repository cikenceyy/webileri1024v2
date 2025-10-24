<?php

namespace App\Modules\HR\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\HR\Domain\Models\EmploymentType;
use App\Modules\HR\Http\Requests\Admin\EmploymentTypeStoreRequest;
use App\Modules\HR\Http\Requests\Admin\EmploymentTypeUpdateRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmploymentTypeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmploymentType::class);

        $query = EmploymentType::query()->where('company_id', currentCompanyId())->orderBy('name');
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

        /** @var LengthAwarePaginator $types */
        $types = $query->paginate()->withQueryString();

        return view('hr::admin.settings.employment-types.index', [
            'types' => $types,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', EmploymentType::class);

        return view('hr::admin.settings.employment-types.create');
    }

    public function store(EmploymentTypeStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = currentCompanyId();

        EmploymentType::create($data);

        return redirect()
            ->route('admin.hr.settings.employment-types.index')
            ->with('status', __('Çalışma tipi oluşturuldu.'));
    }

    public function edit(EmploymentType $employmentType): View
    {
        $this->authorize('update', $employmentType);

        return view('hr::admin.settings.employment-types.edit', [
            'employmentType' => $employmentType,
        ]);
    }

    public function update(EmploymentTypeUpdateRequest $request, EmploymentType $employmentType): RedirectResponse
    {
        $employmentType->update($request->validated());

        return redirect()
            ->route('admin.hr.settings.employment-types.index')
            ->with('status', __('Çalışma tipi güncellendi.'));
    }

    public function destroy(EmploymentType $employmentType): RedirectResponse
    {
        $this->authorize('delete', $employmentType);

        $employmentType->delete();

        return redirect()
            ->route('admin.hr.settings.employment-types.index')
            ->with('status', __('Çalışma tipi silindi.'));
    }
}
