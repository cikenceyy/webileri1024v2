<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use App\Modules\Inventory\Domain\Models\StockItem;
use App\Modules\Inventory\Domain\Models\StockMovement;
use App\Modules\Inventory\Domain\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function onHand(Request $request)
    {
        $this->authorize('viewReports', StockMovement::class);
        $companyId = $this->companyIdOrFail($request);

        $filters = [
            'warehouse_id' => $request->query('warehouse_id'),
            'product_id' => $request->query('product_id'),
            'variant_id' => $request->query('variant_id'),
        ];

        $items = StockItem::query()
            ->with(['warehouse', 'product', 'variant'])
            ->where('company_id', $companyId)
            ->when($filters['warehouse_id'], fn ($q, $warehouseId) => $q->where('warehouse_id', $warehouseId))
            ->when($filters['product_id'], fn ($q, $productId) => $q->where('product_id', $productId))
            ->when($filters['variant_id'], function ($q, $variantId) {
                return $q->where('variant_id', $variantId);
            })
            ->orderBy('warehouse_id')
            ->orderBy('product_id')
            ->get();

        if ($request->query('download') === 'csv') {
            return $this->exportOnHandCsv($items);
        }

        return view('inventory::stock.onhand', [
            'items' => $items,
            'filters' => $filters,
            'warehouses' => $this->warehouseOptions($companyId),
            'products' => $this->productOptions($companyId),
            'variants' => $this->variantOptions($companyId),
        ]);
    }

    public function ledger(Request $request): View
    {
        $this->authorize('viewReports', StockMovement::class);
        $companyId = $this->companyIdOrFail($request);

        $filters = [
            'warehouse_id' => $request->query('warehouse_id'),
            'product_id' => $request->query('product_id'),
            'variant_id' => $request->query('variant_id'),
            'reason' => $request->query('reason'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        $movements = StockMovement::query()
            ->with(['warehouse', 'product', 'variant', 'mover'])
            ->where('company_id', $companyId)
            ->when($filters['warehouse_id'], fn ($q, $warehouseId) => $q->where('warehouse_id', $warehouseId))
            ->when($filters['product_id'], fn ($q, $productId) => $q->where('product_id', $productId))
            ->when($filters['variant_id'], function ($q, $variantId) {
                return $q->where('variant_id', $variantId);
            })
            ->when($filters['reason'], fn ($q, $reason) => $q->where('reason', $reason))
            ->when($filters['date_from'], fn ($q, $from) => $q->where('moved_at', '>=', $from . ' 00:00:00'))
            ->when($filters['date_to'], fn ($q, $to) => $q->where('moved_at', '<=', $to . ' 23:59:59'))
            ->orderByDesc('moved_at')
            ->paginate(25)
            ->withQueryString();

        return view('inventory::stock.ledger', [
            'movements' => $movements,
            'filters' => $filters,
            'warehouses' => $this->warehouseOptions($companyId),
            'products' => $this->productOptions($companyId),
            'variants' => $this->variantOptions($companyId),
            'reasons' => StockMovement::REASONS,
        ]);
    }

    public function valuation(Request $request): View
    {
        $this->authorize('viewReports', StockMovement::class);
        $companyId = $this->companyIdOrFail($request);

        $totals = StockMovement::query()
            ->where('company_id', $companyId)
            ->selectRaw('product_id, variant_id, COALESCE(SUM(CASE WHEN direction = "in" THEN qty ELSE -qty END),0) as qty')
            ->selectRaw('COALESCE(SUM(CASE WHEN direction = "in" THEN qty * unit_cost ELSE -qty * COALESCE(unit_cost,0) END),0) as value')
            ->groupBy('product_id', 'variant_id')
            ->get();

        $products = Product::query()
            ->where('company_id', $companyId)
            ->get(['id', 'name', 'sku']);

        $variants = ProductVariant::query()
            ->where('company_id', $companyId)
            ->get(['id', 'product_id', 'sku', 'options']);

        $rows = $totals->map(function ($row) use ($products, $variants) {
            $product = $products->firstWhere('id', $row->product_id);
            $variant = $row->variant_id ? $variants->firstWhere('id', $row->variant_id) : null;
            $qty = (float) $row->qty;
            $value = (float) $row->value;
            $avgCost = $qty > 0 ? $value / $qty : 0.0;

            return [
                'product' => $product,
                'variant' => $variant,
                'qty' => $qty,
                'avg_cost' => $avgCost,
                'value' => $qty > 0 ? $avgCost * $qty : 0.0,
            ];
        })->filter(fn ($row) => $row['product'] !== null);

        $totalValue = $rows->sum('value');

        return view('inventory::stock.valuation', [
            'rows' => $rows,
            'totalValue' => $totalValue,
        ]);
    }

    protected function companyIdOrFail(Request $request): int
    {
        $companyId = $request->attributes->get('company_id') ?? (app()->bound('company') ? app('company')->id : null);

        if (! $companyId) {
            throw ValidationException::withMessages([
                'company_id' => 'Şirket bağlamı bulunamadı.',
            ]);
        }

        return (int) $companyId;
    }

    protected function warehouseOptions(int $companyId): Collection
    {
        return Warehouse::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();
    }

    protected function productOptions(int $companyId): Collection
    {
        return Product::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);
    }

    protected function variantOptions(int $companyId): Collection
    {
        return ProductVariant::query()
            ->where('company_id', $companyId)
            ->with('product:id,name')
            ->orderBy('sku')
            ->get();
    }

    protected function exportOnHandCsv(Collection $items)
    {
        $headers = ['Warehouse', 'Product', 'Variant', 'Quantity', 'Reserved', 'Reorder Point'];
        $rows = $items->map(function (StockItem $item) {
            return [
                $item->warehouse?->name,
                $item->product?->name,
                $item->variant?->sku,
                $item->qty,
                $item->reserved_qty,
                $item->reorder_point,
            ];
        });

        $callback = function () use ($headers, $rows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, $headers);
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        };

        return Response::streamDownload($callback, 'onhand.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
