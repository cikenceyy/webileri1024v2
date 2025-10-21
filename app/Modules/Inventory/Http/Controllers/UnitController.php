<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Unit;
use App\Modules\Inventory\Http\Requests\StoreUnitRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Unit::class, 'unit');
    }

    public function index(Request $request): View
    {
        $companyId = $this->companyId($request);
        $term = $request->query('q');

        $units = Unit::query()
            ->where('company_id', $companyId)
            ->search($term)
            ->orderBy('code')
            ->paginate(20)
            ->withQueryString();

        return view('inventory::units.index', [
            'units' => $units,
            'filters' => ['q' => $term],
        ]);
    }

    public function create(): View
    {
        return view('inventory::units.create', [
            'unit' => new Unit(['to_base' => 1, 'is_base' => false]),
        ]);
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $companyId = $this->companyIdOrFail($request);
        $data = $request->validated();
        $data['company_id'] = $companyId;
        $data['is_base'] = (bool) ($data['is_base'] ?? false);

        if ($data['is_base']) {
            Unit::query()->where('company_id', $companyId)->update(['is_base' => false]);
            $data['to_base'] = 1;
        }

        Unit::create($data);

        return redirect()->route('admin.inventory.units.index')->with('status', 'Birim kaydedildi.');
    }

    protected function companyId(Request $request): ?int
    {
        return $request->attributes->get('company_id') ?? (app()->bound('company') ? app('company')->id : null);
    }

    protected function companyIdOrFail(Request $request): int
    {
        $companyId = $this->companyId($request);

        if (! $companyId) {
            throw ValidationException::withMessages([
                'company_id' => 'Şirket bağlamı bulunamadı.',
            ]);
        }

        return $companyId;
    }
}
