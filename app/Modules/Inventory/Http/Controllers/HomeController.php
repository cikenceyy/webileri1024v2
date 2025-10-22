<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\StockItem;
use App\Modules\Inventory\Domain\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $movementsToday = StockMovement::query()
            ->whereDate('moved_at', Carbon::today())
            ->orderByDesc('moved_at')
            ->get();

        $recentMovements = StockMovement::query()
            ->with(['warehouse', 'product'])
            ->orderByDesc('moved_at')
            ->limit(6)
            ->get();

        $stockItems = StockItem::query()
            ->with(['product', 'warehouse'])
            ->get();

        $totalStockValue = $stockItems->sum(function (StockItem $item): float {
            $product = $item->product;

            if (! $product) {
                return 0.0;
            }

            $unitPrice = (float) ($product->price ?? 0);

            return $unitPrice * (float) $item->qty;
        });

        $lowStockItems = $stockItems
            ->filter(function (StockItem $item): bool {
                $threshold = (float) ($item->reorder_point ?? $item->product->reorder_point ?? 0);

                return $threshold > 0 && (float) $item->qty < $threshold;
            })
            ->sortBy(fn (StockItem $item) => (float) $item->qty)
            ->take(6);

        $currencyCode = config('inventory.default_currency', 'TRY');

        $kpis = Collection::make([
            [
                'label' => 'Toplam Stok Değeri',
                'value' => number_format($totalStockValue, 2, ',', '.') . ' ' . $currencyCode,
                'icon' => 'bi-cash-stack',
            ],
            [
                'label' => 'Bugün Hareket',
                'value' => number_format($movementsToday->count()),
                'icon' => 'bi-arrow-repeat',
            ],
            [
                'label' => 'Düşük Stoklu Ürün',
                'value' => number_format($lowStockItems->count()),
                'icon' => 'bi-exclamation-triangle',
            ],
            [
                'label' => 'Bekleyen Transfer',
                'value' => number_format($stockItems->filter(fn (StockItem $item) => (float) $item->reserved_qty > 0)->count()),
                'icon' => 'bi-arrows-move',
            ],
        ]);

        $quickActions = [
            [
                'label' => 'Giriş (IN)',
                'icon' => 'bi-box-arrow-in-down',
                'mode' => 'in',
                'route' => route('admin.inventory.stock.console', ['mode' => 'in']),
            ],
            [
                'label' => 'Çıkış (OUT)',
                'icon' => 'bi-box-arrow-up',
                'mode' => 'out',
                'route' => route('admin.inventory.stock.console', ['mode' => 'out']),
            ],
            [
                'label' => 'Transfer (⇆)',
                'icon' => 'bi-arrow-left-right',
                'mode' => 'transfer',
                'route' => route('admin.inventory.stock.console', ['mode' => 'transfer']),
            ],
            [
                'label' => 'Düzeltme (ADJ)',
                'icon' => 'bi-sliders',
                'mode' => 'adjust',
                'route' => route('admin.inventory.stock.console', ['mode' => 'adjust']),
            ],
            [
                'label' => 'Ürün Ekle',
                'icon' => 'bi-plus-circle',
                'mode' => 'create-product',
                'route' => route('admin.inventory.products.index'),
            ],
        ];

        $timeline = $recentMovements->map(function (StockMovement $movement) {
            $product = $movement->product;
            $warehouse = $movement->warehouse;

            return [
                'id' => $movement->id,
                'timestamp' => optional($movement->moved_at)->format('H:i'),
                'title' => $product?->name ?? 'Stok hareketi',
                'subtitle' => sprintf('%s • %s', strtoupper($movement->direction ?? ''), $warehouse?->name ?? 'Depo'),
                'link' => $product ? route('admin.inventory.products.show', $product) : null,
            ];
        });

        $lowStock = $lowStockItems->map(function (StockItem $item) {
            $product = $item->product;
            $warehouse = $item->warehouse;
            $identifier = $product?->sku ?: ($product?->barcode ?: '#');

            return [
                'id' => $item->id,
                'product' => $product,
                'warehouse' => $warehouse,
                'sku' => $identifier,
                'qty' => (float) $item->qty,
                'threshold' => (float) ($item->reorder_point ?? $product?->reorder_point ?? 0),
                'recommendation' => max(0, ((float) ($item->reorder_point ?? $product?->reorder_point ?? 0)) - (float) $item->qty),
            ];
        });

        return view('inventory::home', [
            'kpis' => $kpis,
            'quickActions' => $quickActions,
            'timeline' => $timeline,
            'lowStock' => $lowStock,
        ]);
    }
}
