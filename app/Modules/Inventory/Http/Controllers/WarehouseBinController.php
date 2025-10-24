<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Models\WarehouseBin;
use App\Modules\Inventory\Http\Requests\StoreWarehouseBinRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class WarehouseBinController extends Controller
{
    public function store(StoreWarehouseBinRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('update', $warehouse);

        if ($bin->warehouse_id !== $warehouse->id) {
            abort(404);
        }

        WarehouseBin::create([
            'company_id' => Auth::user()->company_id,
            'warehouse_id' => $warehouse->id,
            'code' => $request->input('code'),
            'name' => $request->input('name'),
        ]);

        return redirect()
            ->route('admin.inventory.warehouses.show', $warehouse)
            ->with('status', 'Raf eklendi');
    }

    public function update(StoreWarehouseBinRequest $request, Warehouse $warehouse, WarehouseBin $bin): RedirectResponse
    {
        $this->authorize('update', $warehouse);

        if ($bin->warehouse_id !== $warehouse->id) {
            abort(404);
        }

        $bin->fill([
            'code' => $request->input('code'),
            'name' => $request->input('name'),
        ])->save();

        return redirect()
            ->route('admin.inventory.warehouses.show', $warehouse)
            ->with('status', 'Raf güncellendi');
    }

    public function destroy(Warehouse $warehouse, WarehouseBin $bin): RedirectResponse
    {
        $this->authorize('update', $warehouse);

        $bin->delete();

        return redirect()
            ->route('admin.inventory.warehouses.show', $warehouse)
            ->with('status', 'Raf silindi');
    }
}
