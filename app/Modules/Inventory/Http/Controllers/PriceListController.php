<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\PriceList;
use App\Modules\Inventory\Domain\Models\PriceListItem;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use App\Modules\Inventory\Http\Requests\StorePriceListRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PriceListController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PriceList::class, 'pricelist');
    }

    public function index(Request $request): View
    {
        $companyId = $this->companyId($request);
        $term = $request->query('q');

        $lists = PriceList::query()
            ->where('company_id', $companyId)
            ->search($term)
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('inventory::pricelists.index', [
            'priceLists' => $lists,
            'filters' => ['q' => $term],
        ]);
    }

    public function create(): View
    {
        return view('inventory::pricelists.create', [
            'priceList' => new PriceList(['currency' => config('inventory.default_currency', 'TRY'), 'type' => 'sale', 'active' => true]),
        ]);
    }

    public function store(StorePriceListRequest $request): RedirectResponse
    {
        $companyId = $this->companyIdOrFail($request);
        $data = $request->validated();
        $data['company_id'] = $companyId;
        $data['active'] = (bool) ($data['active'] ?? false);

        PriceList::create($data);

        return redirect()->route('admin.inventory.pricelists.index')->with('status', 'Fiyat listesi oluşturuldu.');
    }

    public function show(Request $request, PriceList $pricelist): View
    {
        $companyId = $this->companyId($request);
        $pricelist->load(['items.product', 'items.variant']);

        return view('inventory::pricelists.show', [
            'priceList' => $pricelist,
            'products' => $this->productOptions($companyId),
        ]);
    }

    public function edit(PriceList $pricelist): View
    {
        return view('inventory::pricelists.edit', [
            'priceList' => $pricelist,
        ]);
    }

    public function update(StorePriceListRequest $request, PriceList $pricelist): RedirectResponse
    {
        $data = $request->validated();
        $data['active'] = (bool) ($data['active'] ?? false);

        $pricelist->update($data);

        return redirect()->route('admin.inventory.pricelists.index')->with('status', 'Fiyat listesi güncellendi.');
    }

    public function destroy(PriceList $pricelist): RedirectResponse
    {
        $pricelist->delete();

        return redirect()->route('admin.inventory.pricelists.index')->with('status', 'Fiyat listesi silindi.');
    }

    public function storeItem(Request $request, PriceList $pricelist): RedirectResponse
    {
        $this->authorize('update', $pricelist);
        $companyId = $this->companyIdOrFail($request);

        $data = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('company_id', $companyId)],
            'variant_id' => ['nullable', 'integer', Rule::exists('product_variants', 'id')->where('company_id', $companyId)],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        if (! $this->productBelongsToCompany($data['product_id'], $companyId)) {
            throw ValidationException::withMessages(['product_id' => 'Ürün bu şirkete ait değil.']);
        }

        if (! empty($data['variant_id']) && ! $this->variantBelongsToProduct($data['variant_id'], $data['product_id'], $companyId)) {
            throw ValidationException::withMessages(['variant_id' => 'Seçilen varyant ürüne ait değil.']);
        }

        $variantId = $data['variant_id'] ?? null;

        if ($variantId === '') {
            $variantId = null;
        }

        $existing = PriceListItem::query()
            ->where('company_id', $companyId)
            ->where('price_list_id', $pricelist->id)
            ->where('product_id', $data['product_id'])
            ->when($variantId, fn ($query) => $query->where('variant_id', $variantId), fn ($query) => $query->whereNull('variant_id'))
            ->first();

        if ($existing) {
            $existing->update(['price' => $data['price']]);

            return back()->with('status', 'Fiyat satırı güncellendi.');
        }

        PriceListItem::create([
            'company_id' => $companyId,
            'price_list_id' => $pricelist->id,
            'product_id' => $data['product_id'],
            'variant_id' => $variantId,
            'price' => $data['price'],
        ]);

        return back()->with('status', 'Fiyat satırı eklendi.');
    }

    public function destroyItem(PriceList $pricelist, PriceListItem $item): RedirectResponse
    {
        $this->authorize('update', $pricelist);

        if ((int) $item->price_list_id !== (int) $pricelist->id) {
            abort(404);
        }

        $item->delete();

        return back()->with('status', 'Fiyat satırı silindi.');
    }

    protected function productOptions(int $companyId)
    {
        return Product::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->with('variants')
            ->get();
    }

    protected function productBelongsToCompany(int $productId, int $companyId): bool
    {
        return Product::query()->where('company_id', $companyId)->where('id', $productId)->exists();
    }

    protected function variantBelongsToProduct(int $variantId, int $productId, int $companyId): bool
    {
        return ProductVariant::query()
            ->where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('id', $variantId)
            ->exists();
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
}
