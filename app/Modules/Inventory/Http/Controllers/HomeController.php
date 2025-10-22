<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\StockItem;
use App\Modules\Inventory\Domain\Models\StockMovement;
use App\Modules\Inventory\Domain\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $data = $this->composeDashboardData();

        return view('inventory::home', $data);
    }

    public function metrics(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        return response()->json($this->buildKpis());
    }

    public function timeline(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        return response()->json($this->buildTimeline());
    }

    public function lowstock(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        return response()->json($this->buildLowStock());
    }

    protected function composeDashboardData(): array
    {
        return [
            'kpis' => Collection::make($this->buildKpis()),
            'quickActions' => $this->quickActions(),
            'timeline' => Collection::make($this->buildTimeline()),
            'lowStock' => Collection::make($this->buildLowStock()),
            'warehouses' => Warehouse::query()
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    protected function buildKpis(): array
    {
        $movementsToday = StockMovement::query()
            ->whereDate('moved_at', Carbon::today())
            ->count();

        $stockItems = $this->stockItems();

        $totalStockValue = $stockItems->sum(function (StockItem $item): float {
            $product = $item->product;

            if (! $product) {
                return 0.0;
            }

            $unitPrice = (float) ($product->price ?? 0);

            return $unitPrice * (float) $item->qty;
        });

        $lowStockCount = $stockItems
            ->filter(fn (StockItem $item) => $this->isLowStock($item))
            ->count();

        $pendingTransfers = $stockItems
            ->filter(fn (StockItem $item) => (float) $item->reserved_qty > 0)
            ->count();

        $currencyCode = config('inventory.default_currency', 'TRY');

        return [
            [
                'label' => 'Toplam Stok Değeri',
                'value' => number_format($totalStockValue, 2, ',', '.') . ' ' . $currencyCode,
                'icon' => 'bi-cash-stack',
            ],
            [
                'label' => 'Bugün Hareket',
                'value' => number_format($movementsToday),
                'icon' => 'bi-arrow-repeat',
            ],
            [
                'label' => 'Düşük Stoklu Ürün',
                'value' => number_format($lowStockCount),
                'icon' => 'bi-exclamation-triangle',
            ],
            [
                'label' => 'Bekleyen Transfer',
                'value' => number_format($pendingTransfers),
                'icon' => 'bi-arrows-move',
            ],
        ];
    }

    protected function quickActions(): array
    {
        return [
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
                'route' => route('admin.inventory.products.index', ['open' => 'create']),
            ],
        ];
    }

    protected function buildTimeline(): array
    {
        return StockMovement::query()
            ->with(['warehouse', 'product'])
            ->orderByDesc('moved_at')
            ->limit(6)
            ->get()
            ->map(function (StockMovement $movement) {
                $product = $movement->product;
                $warehouse = $movement->warehouse;
                $timestamp = optional($movement->moved_at);

                return [
                    'id' => $movement->id,
                    'title' => $product?->name ?? 'Stok hareketi',
                    'subtitle' => sprintf('%s • %s', strtoupper($movement->direction ?? ''), $warehouse?->name ?? 'Depo'),
                    'timestamp' => $timestamp?->toIso8601String(),
                    'timeLabel' => $timestamp?->format('H:i') ?? '--:--',
                    'link' => $product ? route('admin.inventory.products.show', $product) : null,
                ];
            })
            ->values()
            ->all();
    }

    protected function buildLowStock(): array
    {
        return $this->stockItems()
            ->filter(fn (StockItem $item) => $this->isLowStock($item))
            ->sortBy(fn (StockItem $item) => (float) $item->qty)
            ->take(6)
            ->map(function (StockItem $item) {
                $product = $item->product;
                $warehouse = $item->warehouse;
                $threshold = (float) ($item->reorder_point ?? $product?->reorder_point ?? 0);
                $available = (float) $item->qty;

                return [
                    'id' => $item->id,
                    'productId' => $product?->id,
                    'name' => $product?->name ?? 'Ürün',
                    'warehouse' => $warehouse?->name ?? 'Depo',
                    'warehouseId' => $warehouse?->id,
                    'sku' => $product?->sku ?: ($product?->barcode ?? '#'),
                    'available' => $available,
                    'threshold' => $threshold,
                    'recommendation' => max(0, $threshold - $available),
                    'isCritical' => $available <= 0,
                ];
            })
            ->values()
            ->all();
    }

    protected function stockItems(): Collection
    {
        return StockItem::query()
            ->with(['product', 'warehouse'])
            ->get();
    }

    protected function isLowStock(StockItem $item): bool
    {
        $threshold = (float) ($item->reorder_point ?? $item->product?->reorder_point ?? 0);

        return $threshold > 0 && (float) $item->qty < $threshold;
    }
}
