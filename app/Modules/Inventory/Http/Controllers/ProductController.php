<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Inventory\Domain\Models\PriceList;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductCategory;
use App\Modules\Inventory\Domain\Models\ProductGallery;
use App\Modules\Inventory\Domain\Models\StockItem;
use App\Modules\Inventory\Domain\Models\StockMovement;
use App\Modules\Inventory\Domain\Models\Unit;
use App\Modules\Inventory\Http\Requests\StoreProductRequest;
use App\Modules\Inventory\Http\Requests\UpdateProductRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(Request $request): View
    {
        $companyId = $this->companyId($request);
        $filters = [
            'q' => $request->query('q'),
            'status' => $request->query('status'),
            'category_id' => $request->query('category_id'),
            'sort' => $request->query('sort'),
            'dir' => $request->query('dir'),
        ];

        $sortable = ['name', 'sku', 'created_at'];
        $sort = in_array($filters['sort'], $sortable, true) ? $filters['sort'] : 'created_at';
        $direction = $filters['dir'] === 'asc' ? 'asc' : 'desc';

        $products = Product::query()
            ->with(['media', 'category'])
            ->search($filters['q'])
            ->category($filters['category_id'])
            ->when(in_array($filters['status'], ['active', 'inactive'], true), fn ($q) => $q->where('status', $filters['status']))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        $categories = ProductCategory::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return view('inventory::products.index', [
            'products' => $products,
            'filters' => $filters,
            'sort' => $sort,
            'direction' => $direction,
            'categories' => $categories,
        ]);
    }

    public function create(Request $request): View
    {
        $companyId = $this->companyId($request);

        return view('inventory::products.create', [
            'product' => new Product(['status' => 'active']),
            'categories' => $this->categoryOptions($companyId),
            'units' => $this->unitOptions($companyId),
            'selectedMedia' => $this->selectedMediaForForm($request),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $companyId = $this->companyIdOrFail($request);
        $data = $request->validated();
        $data['company_id'] = $companyId;
        $data['status'] = $data['status'] ?? 'active';
        $data['price'] = $data['price'] ?? 0;
        $data['unit'] = $data['unit'] ?: config('inventory.default_unit', 'pcs');
        $data['reorder_point'] = $data['reorder_point'] ?? 0;
        $data['category_id'] = $this->ensureCategoryBelongsToCompany($data['category_id'] ?? null, $companyId);
        $data['base_unit_id'] = $this->ensureUnitBelongsToCompany($data['base_unit_id'] ?? null, $companyId);
        $data['media_id'] = $this->ensureMediaBelongsToCompany($data['media_id'] ?? null, $companyId);

        Product::create($data);

        return redirect()
            ->route('admin.inventory.products.index')
            ->with('status', 'Ürün başarıyla oluşturuldu.');
    }

    public function show(Request $request, Product $product): View
    {
        $companyId = $this->companyId($request);
        $product->load(['media', 'category', 'baseUnit', 'gallery.media', 'variants']);

        $priceLists = PriceList::query()
            ->where('company_id', $companyId)
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->with(['items' => fn ($q) => $q->where('product_id', $product->id)->whereNull('variant_id')])
            ->get();

        $stockByWarehouse = StockItem::query()
            ->with('warehouse')
            ->where('company_id', $companyId)
            ->where('product_id', $product->id)
            ->whereNull('variant_id')
            ->orderBy('warehouse_id')
            ->get();

        $variantStock = StockItem::query()
            ->with(['warehouse', 'variant'])
            ->where('company_id', $companyId)
            ->where('product_id', $product->id)
            ->whereNotNull('variant_id')
            ->orderBy('warehouse_id')
            ->get();

        $recentMovements = StockMovement::query()
            ->with('warehouse')
            ->where('company_id', $companyId)
            ->where('product_id', $product->id)
            ->orderByDesc('moved_at')
            ->limit(10)
            ->get();

        return view('inventory::products.show', [
            'product' => $product,
            'priceLists' => $priceLists,
            'stockByWarehouse' => $stockByWarehouse,
            'variantStock' => $variantStock,
            'recentMovements' => $recentMovements,
        ]);
    }

    public function edit(Request $request, Product $product): View
    {
        $companyId = $this->companyId($request);
        $product->load('media');

        return view('inventory::products.edit', [
            'product' => $product,
            'categories' => $this->categoryOptions($companyId),
            'units' => $this->unitOptions($companyId),
            'selectedMedia' => $this->selectedMediaForForm($request, $product),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $companyId = $this->companyIdOrFail($request);
        $data = $request->validated();
        $data['price'] = $data['price'] ?? 0;
        $data['unit'] = $data['unit'] ?: config('inventory.default_unit', 'pcs');
        $data['reorder_point'] = $data['reorder_point'] ?? 0;
        $data['category_id'] = $this->ensureCategoryBelongsToCompany($data['category_id'] ?? null, $companyId);
        $data['base_unit_id'] = $this->ensureUnitBelongsToCompany($data['base_unit_id'] ?? null, $companyId);
        $data['media_id'] = $this->ensureMediaBelongsToCompany($data['media_id'] ?? null, $companyId);

        $product->update($data);

        return redirect()
            ->route('admin.inventory.products.index')
            ->with('status', 'Ürün güncellendi.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.inventory.products.index')
            ->with('status', 'Ürün silindi.');
    }

    public function addGallery(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('attachMedia', $product);
        $companyId = $this->companyIdOrFail($request);

        $request->validate([
            'media_id' => ['required', 'integer'],
        ]);

        $mediaId = $this->ensureMediaBelongsToCompany($request->integer('media_id'), $companyId, allowDocuments: true);

        $nextOrder = (int) $product->gallery()->max('sort_order') + 1;

        ProductGallery::create([
            'company_id' => $companyId,
            'product_id' => $product->id,
            'media_id' => $mediaId,
            'sort_order' => $nextOrder,
        ]);

        return back()->with('status', 'Görsel eklendi.');
    }

    public function removeGallery(Request $request, Product $product, ProductGallery $gallery): RedirectResponse
    {
        $this->authorize('attachMedia', $product);

        if ((int) $gallery->product_id !== (int) $product->id) {
            abort(404);
        }

        $gallery->delete();

        return back()->with('status', 'Görsel kaldırıldı.');
    }

    protected function selectedMediaForForm(Request $request, ?Product $product = null): ?Media
    {
        $mediaId = $request->session()->getOldInput('media_id') ?? $product?->media_id;

        if (! $mediaId) {
            return null;
        }

        $companyId = $this->companyId($request);
        if (! $companyId) {
            return null;
        }

        return Media::query()
            ->where('company_id', $companyId)
            ->where('category', Media::CATEGORY_MEDIA_PRODUCTS)
            ->find($mediaId);
    }

    protected function ensureMediaBelongsToCompany(?int $mediaId, ?int $companyId, bool $allowDocuments = false): ?int
    {
        if (! $mediaId) {
            return null;
        }

        if (! $companyId) {
            throw ValidationException::withMessages([
                'media_id' => 'Şirket bağlamı bulunamadı.',
            ]);
        }

        $query = Media::query()
            ->where('company_id', $companyId)
            ->whereNull('deleted_at');

        if ($allowDocuments) {
            $query->whereIn('category', [Media::CATEGORY_MEDIA_PRODUCTS, Media::CATEGORY_DOCUMENTS, Media::CATEGORY_MEDIA_CATALOGS]);
        } else {
            $query->where('category', Media::CATEGORY_MEDIA_PRODUCTS);
        }

        $media = $query->find($mediaId);

        if (! $media) {
            throw ValidationException::withMessages([
                'media_id' => 'Seçilen medya öğesi bulunamadı.',
            ]);
        }

        return $media->id;
    }

    protected function ensureCategoryBelongsToCompany(?int $categoryId, int $companyId): ?int
    {
        if (! $categoryId) {
            return null;
        }

        $exists = ProductCategory::query()
            ->where('company_id', $companyId)
            ->where('id', $categoryId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'category_id' => 'Seçilen kategori bu şirkete ait değil.',
            ]);
        }

        return $categoryId;
    }

    protected function ensureUnitBelongsToCompany(?int $unitId, int $companyId): ?int
    {
        if (! $unitId) {
            return null;
        }

        $exists = Unit::query()
            ->where('company_id', $companyId)
            ->where('id', $unitId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'base_unit_id' => 'Seçilen birim bu şirkete ait değil.',
            ]);
        }

        return $unitId;
    }

    protected function companyId(Request $request): ?int
    {
        return $request->attributes->get('company_id') ?? (app()->bound('company') ? app('company')->id : null);
    }

    protected function companyIdOrFail(Request $request): int
    {
        $companyId = $this->companyId($request);

        if (! $companyId) {
            throw ValidationException::withMessages([
                'company_id' => 'Şirket bağlamı bulunamadı.',
            ]);
        }

        return $companyId;
    }

    protected function categoryOptions(int $companyId): EloquentCollection
    {
        return ProductCategory::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();
    }

    protected function unitOptions(int $companyId): EloquentCollection
    {
        return Unit::query()
            ->where('company_id', $companyId)
            ->orderBy('code')
            ->get();
    }
}
