<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\StockItem;
use App\Modules\Inventory\Domain\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Warehouse::class);

        $warehouses = Warehouse::query()
            ->orderBy('name')
            ->get();

        $stats = StockItem::query()
            ->selectRaw('warehouse_id, SUM(qty) as total_qty, COUNT(*) as line_count')
            ->groupBy('warehouse_id')
            ->get()
            ->keyBy('warehouse_id');

        return view('inventory::warehouses.index', [
            'warehouses' => $warehouses,
            'stats' => $stats,
        ]);
    }

    public function show(Warehouse $warehouse): View
    {
        $this->authorize('view', $warehouse);

        $stockItems = StockItem::query()
            ->with('product')
            ->where('warehouse_id', $warehouse->id)
            ->orderByDesc('qty')
            ->paginate(20);

        return view('inventory::warehouses.show', [
            'warehouse' => $warehouse,
            'stockItems' => $stockItems,
        ]);
    }
}
