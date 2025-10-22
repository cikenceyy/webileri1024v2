<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\StockItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BomController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::query()
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('inventory::bom.index', [
            'products' => $products,
        ]);
    }

    public function show(Product $product, Request $request): View
    {
        $this->authorize('view', $product);

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

        return view('inventory::bom.show', [
            'product' => $product,
            'lot' => $lot,
            'components' => $components,
            'onHand' => (float) $stock,
        ]);
    }
}
