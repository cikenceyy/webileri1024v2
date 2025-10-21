<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Http\Requests\StoreWarehouseRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Warehouse::class, 'warehouse');
    }

    public function index(Request $request): View
    {
        $companyId = $this->companyId($request);
        $term = $request->query('q');

        $warehouses = Warehouse::query()
            ->where('company_id', $companyId)
            ->search($term)
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('inventory::warehouses.index', [
            'warehouses' => $warehouses,
            'filters' => ['q' => $term],
        ]);
    }

    public function create(): View
    {
        return view('inventory::warehouses.create', [
            'warehouse' => new Warehouse(['status' => 'active', 'is_default' => false]),
        ]);
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $companyId = $this->companyIdOrFail($request);
        $data = $request->validated();
        $data['company_id'] = $companyId;
        $data['is_default'] = (bool) ($data['is_default'] ?? false);

        if ($data['is_default']) {
            Warehouse::query()->where('company_id', $companyId)->update(['is_default' => false]);
        }

        Warehouse::create($data);

        return redirect()->route('admin.inventory.warehouses.index')->with('status', 'Ambar oluşturuldu.');
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('inventory::warehouses.edit', [
            'warehouse' => $warehouse,
        ]);
    }

    public function show(Warehouse $warehouse): View
    {
        return view('inventory::warehouses.show', [
            'warehouse' => $warehouse,
        ]);
    }

    public function update(StoreWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $data = $request->validated();
        $data['is_default'] = (bool) ($data['is_default'] ?? false);

        if ($data['is_default']) {
            Warehouse::query()->where('company_id', $warehouse->company_id)->where('id', '!=', $warehouse->id)->update(['is_default' => false]);
        }

        $warehouse->update($data);

        return redirect()->route('admin.inventory.warehouses.index')->with('status', 'Ambar güncellendi.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->delete();

        return redirect()->route('admin.inventory.warehouses.index')->with('status', 'Ambar silindi.');
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
