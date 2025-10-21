<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use App\Modules\Inventory\Domain\Models\StockItem;
use App\Modules\Inventory\Domain\Models\StockMovement;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Services\StockService;
use App\Modules\Inventory\Http\Requests\StoreStockAdjustRequest;
use App\Modules\Inventory\Http\Requests\StoreStockInRequest;
use App\Modules\Inventory\Http\Requests\StoreStockOutRequest;
use App\Modules\Inventory\Http\Requests\StoreStockTransferRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
    public function __construct(private readonly StockService $stockService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', StockMovement::class);
        $companyId = $this->companyIdOrFail($request);

        $filters = [
            'q' => $request->query('q'),
            'warehouse_id' => $request->query('warehouse_id'),
            'status' => $request->query('status'),
        ];

        $items = StockItem::query()
            ->with(['product.media', 'variant', 'warehouse'])
            ->where('company_id', $companyId)
            ->when($filters['warehouse_id'], fn ($q, $warehouseId) => $q->where('warehouse_id', $warehouseId))
            ->when($filters['status'] === 'low', fn ($q) => $q->whereColumn('qty', '<', 'reorder_point'))
            ->when($filters['q'], function ($query, $term) {
                $query->whereHas('product', function ($productQuery) use ($term) {
                    $productQuery->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $warehouses = Warehouse::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return view('inventory::stock.index', [
            'items' => $items,
            'filters' => $filters,
            'warehouses' => $warehouses,
        ]);
    }

    public function inForm(Request $request): View
    {
        $this->authorize('move', StockMovement::class);
        $companyId = $this->companyIdOrFail($request);

        return view('inventory::stock.in', [
            'warehouses' => $this->warehouseOptions($companyId),
            'products' => $this->productOptions($companyId),
            'variants' => $this->variantOptions($companyId),
        ]);
    }

    public function storeIn(StoreStockInRequest $request): RedirectResponse
    {
        $this->authorize('move', StockMovement::class);
        $companyId = $this->companyIdOrFail($request);
        $data = $request->validated();

        [$warehouse, $product, $variant] = $this->resolveTargets($companyId, $data);

        $this->stockService->receive(
            $warehouse,
            $product,
            $variant,
            (float) $data['qty'],
            isset($data['unit_cost']) ? (float) $data['unit_cost'] : null,
            $this->contextFromData($data, $request)
        );

        return redirect()
            ->route('admin.inventory.stock.index')
            ->with('status', 'Stok girişi kaydedildi.');
    }

    public function outForm(Request $request): View
    {
        $this->authorize('move', StockMovement::class);
        $companyId = $this->companyIdOrFail($request);

        return view('inventory::stock.out', [
            'warehouses' => $this->warehouseOptions($companyId),
            'products' => $this->productOptions($companyId),
            'variants' => $this->variantOptions($companyId),
            'reasons' => StockMovement::REASONS,
        ]);
    }

    public function storeOut(StoreStockOutRequest $request): RedirectResponse
    {
        $this->authorize('move', StockMovement::class);
        $companyId = $this->companyIdOrFail($request);
        $data = $request->validated();

        [$warehouse, $product, $variant] = $this->resolveTargets($companyId, $data);

        $this->stockService->issue(
            $warehouse,
            $product,
            $variant,
            (float) $data['qty'],
            $this->contextFromData($data, $request)
        );

        return redirect()
            ->route('admin.inventory.stock.index')
            ->with('status', 'Stok çıkışı kaydedildi.');
    }

    public function transferForm(Request $request): View
    {
        $this->authorize('transfer', StockMovement::class);
        $companyId = $this->companyIdOrFail($request);

        return view('inventory::stock.transfer', [
            'warehouses' => $this->warehouseOptions($companyId),
            'products' => $this->productOptions($companyId),
            'variants' => $this->variantOptions($companyId),
        ]);
    }

    public function storeTransfer(StoreStockTransferRequest $request): RedirectResponse
    {
        $this->authorize('transfer', StockMovement::class);
        $companyId = $this->companyIdOrFail($request);
        $data = $request->validated();

        $fromWarehouse = $this->resolveWarehouse($companyId, (int) $data['from_warehouse_id']);
        $toWarehouse = $this->resolveWarehouse($companyId, (int) $data['to_warehouse_id']);
        $product = $this->resolveProduct($companyId, (int) $data['product_id']);
        $variant = $this->resolveVariant($product, $data['variant_id'] ?? null);

        $this->stockService->transfer(
            $fromWarehouse,
            $toWarehouse,
            $product,
            $variant,
            (float) $data['qty'],
            $this->contextFromData($data, $request)
        );

        return redirect()
            ->route('admin.inventory.stock.index')
            ->with('status', 'Transfer işlemi tamamlandı.');
    }

    public function adjustForm(Request $request): View
    {
        $this->authorize('adjust', StockMovement::class);
        $companyId = $this->companyIdOrFail($request);

        return view('inventory::stock.adjust', [
            'warehouses' => $this->warehouseOptions($companyId),
            'products' => $this->productOptions($companyId),
            'variants' => $this->variantOptions($companyId),
        ]);
    }

    public function storeAdjust(StoreStockAdjustRequest $request): RedirectResponse
    {
        $this->authorize('adjust', StockMovement::class);
        $companyId = $this->companyIdOrFail($request);
        $data = $request->validated();

        [$warehouse, $product, $variant] = $this->resolveTargets($companyId, $data);

        $this->stockService->adjust(
            $warehouse,
            $product,
            $variant,
            (float) $data['qty'],
            isset($data['unit_cost']) ? (float) $data['unit_cost'] : null,
            $this->contextFromData($data, $request)
        );

        return redirect()
            ->route('admin.inventory.stock.index')
            ->with('status', 'Stok düzeltmesi kaydedildi.');
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

    protected function resolveTargets(int $companyId, array $data): array
    {
        $warehouse = $this->resolveWarehouse($companyId, (int) $data['warehouse_id']);
        $product = $this->resolveProduct($companyId, (int) $data['product_id']);
        $variant = $this->resolveVariant($product, $data['variant_id'] ?? null);

        return [$warehouse, $product, $variant];
    }

    protected function resolveWarehouse(int $companyId, int $warehouseId): Warehouse
    {
        $warehouse = Warehouse::query()
            ->where('company_id', $companyId)
            ->find($warehouseId);

        if (! $warehouse) {
            throw ValidationException::withMessages([
                'warehouse_id' => 'Seçilen ambar bu şirkete ait değil.',
            ]);
        }

        return $warehouse;
    }

    protected function resolveProduct(int $companyId, int $productId): Product
    {
        $product = Product::query()
            ->where('company_id', $companyId)
            ->find($productId);

        if (! $product) {
            throw ValidationException::withMessages([
                'product_id' => 'Seçilen ürün bu şirkete ait değil.',
            ]);
        }

        return $product;
    }

    protected function resolveVariant(Product $product, ?int $variantId): ?ProductVariant
    {
        if (! $variantId) {
            return null;
        }

        $variant = ProductVariant::query()
            ->where('company_id', $product->company_id)
            ->where('product_id', $product->id)
            ->find($variantId);

        if (! $variant) {
            throw ValidationException::withMessages([
                'variant_id' => 'Seçilen varyant bu ürüne ait değil.',
            ]);
        }

        return $variant;
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

    protected function contextFromData(array $data, Request $request): array
    {
        return [
            'reason' => $data['reason'] ?? null,
            'ref_type' => $data['ref_type'] ?? null,
            'ref_id' => $data['ref_id'] ?? null,
            'note' => $data['note'] ?? null,
            'moved_at' => $data['moved_at'] ?? null,
            'user_id' => $request->user()?->id,
        ];
    }
}
