<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\StockLedgerEntry;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Models\WarehouseBin;
use App\Modules\Inventory\Http\Requests\StoreWarehouseRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Warehouse::class);

        $warehouses = Warehouse::query()
            ->with('bins')
            ->orderBy('name')
            ->get();

        $companyId = Auth::user()->company_id;

        $stats = StockLedgerEntry::query()
            ->selectRaw('warehouse_id, SUM(qty_in - qty_out) as total_qty')
            ->where('company_id', $companyId)
            ->groupBy('warehouse_id')
            ->get()
            ->keyBy('warehouse_id');

        $selectedWarehouse = null;
        $selectedBin = null;

        if ($request->filled('warehouse_id')) {
            $selectedWarehouse = $warehouses->firstWhere('id', (int) $request->input('warehouse_id'));
        }

        if ($selectedWarehouse && $request->filled('bin_id')) {
            $selectedBin = $selectedWarehouse->bins->firstWhere('id', (int) $request->input('bin_id'));
        }

        $stockQuery = StockLedgerEntry::query()
            ->selectRaw('product_id, warehouse_id, bin_id, SUM(qty_in - qty_out) as qty')
            ->where('company_id', $companyId)
            ->when($selectedWarehouse, fn ($query) => $query->where('warehouse_id', $selectedWarehouse->id))
            ->when($selectedBin, fn ($query) => $query->where('bin_id', $selectedBin->id))
            ->groupBy('product_id', 'warehouse_id', 'bin_id');

        if ($term = $request->string('search')->toString()) {
            $stockQuery->whereIn('product_id', function ($query) use ($term, $companyId) {
                $query->select('id')
                    ->from((new Product())->getTable())
                    ->where('company_id', $companyId)
                    ->where(function ($builder) use ($term) {
                        $builder->where('name', 'like', "%{$term}%")
                            ->orWhere('sku', 'like', "%{$term}%");
                    });
            });
        }

        $stockRows = $stockQuery->havingRaw('SUM(qty_in - qty_out) <> 0')->get();

        $productMap = Product::query()->whereIn('id', $stockRows->pluck('product_id')->filter())->get()->keyBy('id');
        $binMap = WarehouseBin::query()->whereIn('id', $stockRows->pluck('bin_id')->filter())->get()->keyBy('id');

        $stockItems = $stockRows->map(function ($row) use ($productMap, $binMap) {
            return [
                'product' => $productMap->get($row->product_id),
                'bin' => $row->bin_id ? $binMap->get($row->bin_id) : null,
                'warehouse_id' => $row->warehouse_id,
                'qty' => $row->qty,
            ];
        });

        return view('inventory::warehouses.index', [
            'warehouses' => $warehouses,
            'stats' => $stats,
            'selectedWarehouse' => $selectedWarehouse,
            'selectedBin' => $selectedBin,
            'stockItems' => $stockItems,
        ]);
    }

    public function show(Request $request, Warehouse $warehouse): View
    {
        $this->authorize('view', $warehouse);

        $companyId = Auth::user()->company_id;
        $stockQuery = StockLedgerEntry::query()
            ->selectRaw('product_id, bin_id, SUM(qty_in - qty_out) as qty')
            ->where('company_id', $companyId)
            ->where('warehouse_id', $warehouse->id)
            ->groupBy('product_id', 'bin_id');

        if ($term = $request->string('search')->toString()) {
            $stockQuery->whereIn('product_id', function ($query) use ($term, $companyId) {
                $query->select('id')
                    ->from((new Product())->getTable())
                    ->where('company_id', $companyId)
                    ->where(function ($builder) use ($term) {
                        $builder->where('name', 'like', "%{$term}%")
                            ->orWhere('sku', 'like', "%{$term}%");
                    });
            });
        }

        $rows = $stockQuery->havingRaw('SUM(qty_in - qty_out) <> 0')->get();

        $productMap = Product::query()->whereIn('id', $rows->pluck('product_id')->filter())->get()->keyBy('id');
        $binMap = WarehouseBin::query()->whereIn('id', $rows->pluck('bin_id')->filter())->get()->keyBy('id');

        $stockItems = $rows->map(function ($row) use ($productMap, $binMap) {
            return [
                'product' => $productMap->get($row->product_id),
                'bin' => $row->bin_id ? $binMap->get($row->bin_id) : null,
                'qty' => $row->qty,
            ];
        });

        $bins = $warehouse->bins()->orderBy('code')->get();

        return view('inventory::warehouses.show', [
            'warehouse' => $warehouse,
            'bins' => $bins,
            'stockItems' => $stockItems,
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Warehouse::class);

        return view('inventory::warehouses.create');
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $this->authorize('create', Warehouse::class);

        $warehouse = Warehouse::create([
            'company_id' => Auth::user()->company_id,
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'is_default' => (bool) $request->boolean('is_default'),
            'status' => $request->input('status', 'active'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.inventory.warehouses.show', $warehouse)
            ->with('status', 'Depo oluşturuldu');
    }

    public function edit(Warehouse $warehouse): View
    {
        $this->authorize('update', $warehouse);

        return view('inventory::warehouses.edit', [
            'warehouse' => $warehouse,
        ]);
    }

    public function update(StoreWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('update', $warehouse);

        $warehouse->fill([
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'is_default' => (bool) $request->boolean('is_default'),
            'status' => $request->input('status', 'active'),
            'is_active' => $request->boolean('is_active', true),
        ])->save();

        return redirect()
            ->route('admin.inventory.warehouses.show', $warehouse)
            ->with('status', 'Depo güncellendi');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('delete', $warehouse);

        if ($warehouse->bins()->exists()) {
            return back()->withErrors('Depoda raflar mevcut, silinemiyor.');
        }

        $warehouse->delete();

        return redirect()
            ->route('admin.inventory.warehouses.index')
            ->with('status', 'Depo silindi');
    }
}
