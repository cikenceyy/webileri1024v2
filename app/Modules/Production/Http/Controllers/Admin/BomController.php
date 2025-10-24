<?php

namespace App\Modules\Production\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\StockItem;
use App\Modules\Production\Domain\Models\Bom;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BomController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Bom::class);

        $products = Product::query()
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('production::admin.bom.index', [
            'products' => $products,
        ]);
    }

    public function show(Product $product, Request $request): View
    {
        $this->authorize('view', new Bom($product));

        $lot = max(1, (int) $request->integer('lot', 1));

        $components = $product->stockMovements()
            ->orderByDesc('moved_at')
            ->limit(8)
            ->get()
            ->map(function ($movement) use ($lot) {
                $qty = abs((float) $movement->qty) * $lot;

                return [
                    'id' => $movement->id,
                    'material' => $movement->note ?: 'Malzeme',
                    'unit' => 'pcs',
                    'required' => $qty,
                    'available' => max(0, $qty - 2),
                    'status' => $qty > 0 ? 'ok' : 'missing',
                ];
            });

        $stock = StockItem::query()
            ->where('product_id', $product->id)
            ->sum('qty');

        return view('production::admin.bom.show', [
            'product' => $product,
            'lot' => $lot,
            'components' => $components,
            'onHand' => (float) $stock,
        ]);
    }
}
