<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\StockItem;
use App\Modules\Inventory\Domain\Models\StockMovement;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockConsoleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('move', StockMovement::class);

        $mode = $this->normalizeMode($request->query('mode'));

        $warehouses = Warehouse::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $recentProducts = Product::query()
            ->with(['media', 'baseUnit', 'stockItems'])
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get(['id', 'name', 'sku', 'barcode', 'media_id', 'price', 'base_unit_id']);

        return view('inventory::stock.console', [
            'mode' => $mode,
            'warehouses' => $warehouses,
            'recentProducts' => $recentProducts,
            'allowNegative' => (bool) config('inventory.allow_negative_stock', false),
            'defaultMovedAt' => now()->format('Y-m-d\TH:i'),
        ]);
    }

    public function store(Request $request, StockService $stockService): JsonResponse
    {
        $this->authorize('move', StockMovement::class);

        $validator = Validator::make($request->all(), [
            'mode' => ['required', 'string', 'in:in,out,transfer,adjust'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric'],
            'source_warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'target_warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'reference' => ['nullable', 'string', 'max:120'],
            'moved_at' => ['nullable', 'date'],
        ], [], [
            'items.*.id' => 'ürün',
            'items.*.qty' => 'miktar',
            'source_warehouse_id' => 'kaynak depo',
            'target_warehouse_id' => 'hedef depo',
            'reference' => 'belge no',
            'moved_at' => 'tarih',
        ]);

        $validator->after(function ($validator) use ($request): void {
            $mode = $this->normalizeMode($request->input('mode'));
            $source = $request->input('source_warehouse_id');
            $target = $request->input('target_warehouse_id');

            if ($mode === 'in' && empty($target)) {
                $validator->errors()->add('target_warehouse_id', 'Giriş işlemi için hedef depo seçilmelidir.');
            }

            if ($mode === 'out' && empty($source)) {
                $validator->errors()->add('source_warehouse_id', 'Çıkış işlemi için kaynak depo seçilmelidir.');
            }

            if ($mode === 'transfer') {
                if (empty($source) || empty($target)) {
                    $validator->errors()->add('source_warehouse_id', 'Transfer için kaynak ve hedef depo zorunludur.');
                } elseif ((int) $source === (int) $target) {
                    $validator->errors()->add('target_warehouse_id', 'Transfer için farklı depolar seçilmelidir.');
                }
            }

            if ($mode === 'adjust' && empty($source)) {
                $validator->errors()->add('source_warehouse_id', 'Düzeltme işlemi için depo seçilmelidir.');
            }
        });

        $data = $validator->validate();

        $items = collect($data['items'] ?? [])->filter(function ($item) use ($data) {
            $qty = (float) ($item['qty'] ?? 0);

            if ($data['mode'] === 'adjust') {
                return $qty !== 0.0;
            }

            return $qty > 0;
        });

        if ($items->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sepette işlenecek ürün bulunmuyor.',
            ], 422);
        }

        $mode = $data['mode'];
        $sourceWarehouse = ! empty($data['source_warehouse_id'])
            ? Warehouse::query()->findOrFail((int) $data['source_warehouse_id'])
            : null;
        $targetWarehouse = ! empty($data['target_warehouse_id'])
            ? Warehouse::query()->findOrFail((int) $data['target_warehouse_id'])
            : null;

        $productMap = Product::query()
            ->whereIn('id', $items->pluck('id')->all())
            ->get()
            ->keyBy('id');

        $reference = trim((string) ($data['reference'] ?? ''));
        $note = $reference !== '' ? 'Belge: ' . $reference : null;
        $movedAt = ! empty($data['moved_at']) ? Carbon::parse($data['moved_at']) : now();

        $context = array_filter([
            'user_id' => $request->user()?->id,
            'note' => $note,
            'ref_type' => 'console',
            'ref_id' => null,
            'moved_at' => $movedAt,
        ]);

        $movementLog = [];
        $processed = 0;

        try {
            foreach ($items as $index => $item) {
                $product = $productMap->get((int) $item['id']);

                if (! $product) {
                    continue;
                }

                $qty = (float) $item['qty'];

                if ($mode !== 'adjust' && $qty <= 0) {
                    throw ValidationException::withMessages([
                        "items.{$index}.qty" => 'Miktar sıfırdan büyük olmalıdır.',
                    ]);
                }

                ++$processed;

                if ($mode === 'in') {
                    $movement = $stockService->receive(
                        $targetWarehouse,
                        $product,
                        null,
                        $qty,
                        null,
                        array_merge($context, ['reason' => 'purchase'])
                    );

                    $movementLog[] = $this->formatMovementPayload($movement, $product->name, $targetWarehouse);
                    continue;
                }

                if ($mode === 'out') {
                    $movement = $stockService->issue(
                        $sourceWarehouse,
                        $product,
                        null,
                        $qty,
                        array_merge($context, ['reason' => 'sale'])
                    );

                    $movementLog[] = $this->formatMovementPayload($movement, $product->name, $sourceWarehouse);
                    continue;
                }

                if ($mode === 'transfer') {
                    [$outMovement, $inMovement] = $stockService->transfer(
                        $sourceWarehouse,
                        $targetWarehouse,
                        $product,
                        null,
                        $qty,
                        $context
                    );

                    $movementLog[] = $this->formatMovementPayload($outMovement, $product->name, $sourceWarehouse);
                    $movementLog[] = $this->formatMovementPayload($inMovement, $product->name, $targetWarehouse);
                    continue;
                }

                if ($mode === 'adjust') {
                    $movement = $stockService->adjust(
                        $sourceWarehouse,
                        $product,
                        null,
                        $qty,
                        null,
                        array_merge($context, ['reason' => 'adjustment'])
                    );

                    $movementLog[] = $this->formatMovementPayload($movement, $product->name, $sourceWarehouse);
                }
            }
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stok hareketi kaydedilemedi.',
                'errors' => $exception->errors(),
            ], 422);
        }

        $totals = [
            'lines' => $processed,
            'qty' => 0.0,
            'value' => 0.0,
        ];

        foreach ($movementLog as $entry) {
            $totals['qty'] += $entry['signed_qty'];
            $totals['value'] += $entry['signed_qty'] * $entry['unit_cost'];
        }

        return response()->json([
            'status' => 'ok',
            'mode' => $mode,
            'processed' => $processed,
            'movements' => $movementLog,
            'totals' => $totals,
        ]);
    }

    public function lookup(Request $request): JsonResponse
    {
        $this->authorize('move', StockMovement::class);

        $query = trim((string) $request->query('q', ''));

        if ($query === '') {
            return response()->json([], 422);
        }

        $product = Product::query()
            ->with(['media', 'baseUnit'])
            ->where('sku', 'like', "%{$query}%")
            ->orWhere('barcode', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->first(['id', 'name', 'sku', 'barcode', 'media_id', 'price', 'base_unit_id']);

        if (! $product) {
            return response()->json([], 404);
        }

        $onHand = StockItem::query()
            ->where('product_id', $product->id)
            ->sum('qty');

        $media = $product->media;
        $mediaUrl = null;

        if ($media) {
            $disk = $media->disk ?? config('filesystems.default');
            $path = $media->thumb_path ?: $media->path;
            $mediaUrl = $path ? Storage::disk($disk)->url($path) : null;
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'image' => $mediaUrl,
            'price' => (float) ($product->price ?? 0),
            'unit' => $product->baseUnit?->code,
            'onHand' => (float) $onHand,
        ]);
    }

    protected function normalizeMode(?string $mode): string
    {
        $mode = Str::of((string) $mode)->lower()->value();

        return in_array($mode, ['in', 'out', 'transfer', 'adjust'], true) ? $mode : 'in';
    }

    protected function formatMovementPayload(StockMovement $movement, string $productName, ?Warehouse $warehouse): array
    {
        $warehouseName = $warehouse?->name ?? $movement->warehouse?->name;

        return [
            'id' => $movement->id,
            'product_id' => $movement->product_id,
            'product_name' => $productName,
            'warehouse_id' => $warehouse?->id ?? $movement->warehouse_id,
            'warehouse_name' => $warehouseName,
            'direction' => $movement->direction,
            'qty' => (float) $movement->qty,
            'signed_qty' => $movement->direction === StockMovement::DIRECTION_OUT
                ? -1 * (float) $movement->qty
                : (float) $movement->qty,
            'unit_cost' => (float) ($movement->unit_cost ?? 0),
            'note' => $movement->note,
            'moved_at' => optional($movement->moved_at)->toIso8601String(),
        ];
    }
}
