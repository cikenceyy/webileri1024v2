<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use App\Modules\Inventory\Http\Requests\StoreVariantRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    public function index(Request $request, Product $product): View
    {
        $this->authorize('viewAny', ProductVariant::class);
        $this->authorize('view', $product);

        $variants = $product->variants()
            ->orderBy('sku')
            ->paginate(20)
            ->withQueryString();

        return view('inventory::variants.index', [
            'product' => $product,
            'variants' => $variants,
        ]);
    }

    public function create(Product $product): View
    {
        $this->authorize('create', ProductVariant::class);

        return view('inventory::variants.create', [
            'product' => $product,
            'variant' => new ProductVariant(['status' => 'active']),
        ]);
    }

    public function store(StoreVariantRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('create', ProductVariant::class);
        $companyId = $product->company_id;
        $data = $request->validated();
        $data['company_id'] = $companyId;
        $data['product_id'] = $product->id;
        $data['options'] = $this->cleanOptions($data['options'] ?? []);

        ProductVariant::create($data);

        return redirect()->route('admin.inventory.products.variants.index', $product)->with('status', 'Varyant oluşturuldu.');
    }

    public function edit(Product $product, ProductVariant $variant): View
    {
        $this->authorize('update', $variant);

        return view('inventory::variants.edit', [
            'product' => $product,
            'variant' => $variant,
        ]);
    }

    public function update(StoreVariantRequest $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->authorize('update', $variant);
        $data = $request->validated();
        $data['options'] = $this->cleanOptions($data['options'] ?? []);

        $variant->update($data);

        return redirect()->route('admin.inventory.products.variants.index', $product)->with('status', 'Varyant güncellendi.');
    }

    public function destroy(Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->authorize('delete', $variant);
        $variant->delete();

        return redirect()->route('admin.inventory.products.variants.index', $product)->with('status', 'Varyant silindi.');
    }

    protected function cleanOptions(array $options): array
    {
        return collect($options)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->toArray();
    }
}
