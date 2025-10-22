<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\StockItem;
use App\Modules\Inventory\Domain\Models\StockMovement;
use App\Modules\Inventory\Domain\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            ->with('media')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get(['id', 'name', 'sku', 'barcode', 'media_id']);

        return view('inventory::stock.console', [
            'mode' => $mode,
            'warehouses' => $warehouses,
            'recentProducts' => $recentProducts,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('move', StockMovement::class);

        $data = $request->validate([
            'mode' => ['required', 'string', 'in:in,out,transfer,adjust'],
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0'],
        ]);

        return response()->json([
            'status' => 'ok',
            'mode' => $data['mode'],
            'processed' => count($data['items']),
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
            ->with('media')
            ->where('sku', 'like', "%{$query}%")
            ->orWhere('barcode', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->first(['id', 'name', 'sku', 'barcode', 'media_id']);

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
            'onHand' => (float) $onHand,
        ]);
    }

    protected function normalizeMode(?string $mode): string
    {
        $mode = Str::of((string) $mode)->lower()->value();

        return in_array($mode, ['in', 'out', 'transfer', 'adjust'], true) ? $mode : 'in';
    }
}
