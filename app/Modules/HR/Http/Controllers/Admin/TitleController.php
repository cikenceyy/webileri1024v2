<?php

namespace App\Modules\HR\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\HR\Domain\Models\Title;
use App\Modules\HR\Http\Requests\Admin\TitleStoreRequest;
use App\Modules\HR\Http\Requests\Admin\TitleUpdateRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TitleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Title::class);

        $query = Title::query()->where('company_id', currentCompanyId())->orderBy('name');
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

        /** @var LengthAwarePaginator $titles */
        $titles = $query->paginate()->withQueryString();

        return view('hr::admin.settings.titles.index', [
            'titles' => $titles,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Title::class);

        return view('hr::admin.settings.titles.create');
    }

    public function store(TitleStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = currentCompanyId();

        Title::create($data);

        return redirect()
            ->route('admin.hr.settings.titles.index')
            ->with('status', __('Ünvan oluşturuldu.'));
    }

    public function edit(Title $title): View
    {
        $this->authorize('update', $title);

        return view('hr::admin.settings.titles.edit', [
            'title' => $title,
        ]);
    }

    public function update(TitleUpdateRequest $request, Title $title): RedirectResponse
    {
        $title->update($request->validated());

        return redirect()
            ->route('admin.hr.settings.titles.index')
            ->with('status', __('Ünvan güncellendi.'));
    }

    public function destroy(Title $title): RedirectResponse
    {
        $this->authorize('delete', $title);

        $title->delete();

        return redirect()
            ->route('admin.hr.settings.titles.index')
            ->with('status', __('Ünvan silindi.'));
    }
}
