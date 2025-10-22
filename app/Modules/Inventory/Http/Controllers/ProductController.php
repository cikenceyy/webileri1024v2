<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\PriceList;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductCategory;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use App\Modules\Inventory\Domain\Models\StockItem;
use App\Modules\Inventory\Domain\Models\StockMovement;
use App\Modules\Inventory\Domain\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $filters = [
            'q' => $request->query('q'),
            'category' => $request->integer('category'),
            'warehouse' => $request->integer('warehouse'),
            'variant' => $request->integer('variant'),
            'view' => $request->query('view', 'grid'),
        ];

        $query = Product::query()
            ->with(['media', 'stockItems'])
            ->search($filters['q']);

        if ($filters['category']) {
            $query->where('category_id', $filters['category']);
        }

        if ($filters['warehouse']) {
            $query->whereHas('stockItems', function ($builder) use ($filters) {
                $builder->where('warehouse_id', $filters['warehouse']);
            });
        }

        if ($filters['variant']) {
            $query->whereHas('variants', function ($builder) use ($filters) {
                $builder->where('id', $filters['variant']);
            });
        }

        $products = $query
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $categories = ProductCategory::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $warehouses = Warehouse::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $variants = ProductVariant::query()
            ->orderBy('id')
            ->limit(12)
            ->get(['id', 'sku', 'options']);

        return view('inventory::products.index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => $categories,
            'warehouses' => $warehouses,
            'variants' => $variants,
        ]);
    }

    public function show(Product $product): View
    {
        $this->authorize('view', $product);

        $product->load(['media', 'variants', 'gallery.media', 'category', 'baseUnit']);

        $stockByWarehouse = StockItem::query()
            ->with('warehouse')
            ->where('product_id', $product->id)
            ->orderByDesc('qty')
            ->get();

        $onHandTotal = (float) $stockByWarehouse->sum('qty');
        $reorderPoint = (float) ($product->reorder_point ?? 0);

        $consumptionWindow = Carbon::now()->subDays(30);
        $consumption = StockMovement::query()
            ->where('product_id', $product->id)
            ->where('direction', StockMovement::DIRECTION_OUT)
            ->where('moved_at', '>=', $consumptionWindow)
            ->sum('qty');

        $avgDailyConsumption = $consumption > 0 ? $consumption / 30 : 0;
        $depletionDays = $avgDailyConsumption > 0 ? (int) ceil($onHandTotal / $avgDailyConsumption) : null;

        $recentMovements = StockMovement::query()
            ->with('warehouse')
            ->where('product_id', $product->id)
            ->orderByDesc('moved_at')
            ->limit(5)
            ->get();

        $priceLists = PriceList::query()
            ->with(['items' => fn ($query) => $query->where('product_id', $product->id)])
            ->orderBy('name')
            ->get();

        return view('inventory::products.show', [
            'product' => $product,
            'stockByWarehouse' => $stockByWarehouse,
            'recentMovements' => $recentMovements,
            'priceLists' => $priceLists,
            'onHandTotal' => $onHandTotal,
            'reorderPoint' => $reorderPoint,
            'depletionDays' => $depletionDays,
        ]);
    }

    public function components(Product $product, Request $request): View
    {
        $this->authorize('view', $product);

        $lot = max(1, (int) $request->integer('lot', 1));

        $movements = $product->stockMovements()
            ->with('warehouse')
            ->orderByDesc('moved_at')
            ->limit(6)
            ->get();

        $onHandTotal = StockItem::query()
            ->where('product_id', $product->id)
            ->sum('qty');

        $components = $movements->map(function (StockMovement $movement) use ($lot, $onHandTotal) {
            $required = abs((float) $movement->qty) * $lot;
            $warehouse = $movement->warehouse;

            return [
                'id' => $movement->id,
                'name' => $movement->note ?: ($movement->reason ? ucfirst($movement->reason) : 'Bileşen'),
                'sku' => $movement->ref_type ? $movement->ref_type . '#' . $movement->ref_id : 'MOV-' . $movement->id,
                'required' => $required,
                'onHand' => (float) $onHandTotal,
                'warehouse' => $warehouse?->name,
                'timestamp' => optional($movement->moved_at)->toDateTimeString(),
            ];
        });

        return view('inventory::products.components', [
            'product' => $product,
            'lot' => $lot,
            'components' => $components,
        ]);
    }
}
