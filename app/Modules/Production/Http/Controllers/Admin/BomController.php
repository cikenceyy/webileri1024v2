<?php

namespace App\Modules\Production\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Models\WarehouseBin;
use App\Modules\Production\Domain\Models\Bom;
use App\Modules\Production\Domain\Models\BomItem;
use App\Modules\Production\Http\Requests\Admin\BomStoreRequest;
use App\Modules\Production\Http\Requests\Admin\BomUpdateRequest;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BomController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Bom::class);

        $boms = Bom::query()
            ->with(['product', 'variant'])
            ->where('company_id', currentCompanyId())
            ->orderBy('product_id')
            ->paginate(15)
            ->withQueryString();

        return view('production::admin.boms.index', [
            'boms' => $boms,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Bom::class);

        $companyId = currentCompanyId();
        $products = Product::query()->where('company_id', $companyId)->orderBy('name')->get();
        $warehouses = Warehouse::query()->where('company_id', $companyId)->orderBy('name')->get();
        $bins = WarehouseBin::query()->where('company_id', $companyId)->orderBy('code')->get();

        $bom = new Bom([
            'company_id' => $companyId,
        ]);
        $bom->setRelation('items', collect());

        return view('production::admin.boms.create', [
            'products' => $products,
            'warehouses' => $warehouses,
            'bom' => $bom,
            'bins' => $bins,
        ]);
    }

    public function store(BomStoreRequest $request): RedirectResponse
    {
        $companyId = currentCompanyId();
        $data = $request->validated();

        $bom = DB::transaction(function () use ($companyId, $data) {
            $version = $data['version'] ?? $this->nextVersion($companyId, $data['product_id'], $data['variant_id'] ?? null);

            $bom = Bom::create([
                'company_id' => $companyId,
                'product_id' => $data['product_id'],
                'variant_id' => $data['variant_id'] ?? null,
                'code' => $data['code'],
                'version' => $version,
                'output_qty' => $data['output_qty'],
                'is_active' => $data['is_active'] ?? true,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($bom, $data['items'] ?? []);

            return $bom;
        });

        return redirect()->route('admin.production.boms.show', $bom)->with('status', __('BOM oluşturuldu.'));
    }

    public function show(Bom $bom): View
    {
        $this->authorize('view', $bom);
        $bom->load(['product', 'variant', 'items.component', 'items.componentVariant', 'items.defaultWarehouse']);

        return view('production::admin.boms.show', [
            'bom' => $bom,
        ]);
    }

    public function edit(Bom $bom): View
    {
        $this->authorize('update', $bom);

        $companyId = currentCompanyId();
        $bom->load(['items.defaultWarehouse', 'items.defaultBin']);
        $products = Product::query()->where('company_id', $companyId)->orderBy('name')->get();
        $warehouses = Warehouse::query()->where('company_id', $companyId)->orderBy('name')->get();
        $bins = WarehouseBin::query()->where('company_id', $companyId)->orderBy('code')->get();

        return view('production::admin.boms.edit', [
            'bom' => $bom,
            'products' => $products,
            'warehouses' => $warehouses,
            'bins' => $bins,
        ]);
    }

    public function update(BomUpdateRequest $request, Bom $bom): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($bom, $data) {
            $bom->fill([
                'product_id' => $data['product_id'],
                'variant_id' => $data['variant_id'] ?? null,
                'code' => $data['code'],
                'version' => $data['version'] ?? $bom->version,
                'output_qty' => $data['output_qty'],
                'is_active' => $data['is_active'] ?? true,
                'notes' => $data['notes'] ?? null,
            ])->save();

            $this->syncItems($bom, $data['items'] ?? []);
        });

        return redirect()->route('admin.production.boms.show', $bom)->with('status', __('BOM güncellendi.'));
    }

    public function destroy(Bom $bom): RedirectResponse
    {
        $this->authorize('delete', $bom);

        $bom->items()->delete();
        $bom->delete();

        return redirect()->route('admin.production.boms.index')->with('status', __('BOM silindi.'));
    }

    public function duplicate(Bom $bom): RedirectResponse
    {
        $this->authorize('create', Bom::class);

        $bom->load('items');
        $companyId = currentCompanyId();

        $copy = DB::transaction(function () use ($bom, $companyId) {
            $version = $this->nextVersion($companyId, $bom->product_id, $bom->variant_id);
            $newBom = Bom::create([
                'company_id' => $companyId,
                'product_id' => $bom->product_id,
                'variant_id' => $bom->variant_id,
                'code' => $bom->code . '-v' . $version,
                'version' => $version,
                'output_qty' => $bom->output_qty,
                'is_active' => true,
                'notes' => $bom->notes,
            ]);

            $items = $bom->items->map(function (BomItem $item) {
                return [
                    'component_product_id' => $item->component_product_id,
                    'component_variant_id' => $item->component_variant_id,
                    'qty_per' => $item->qty_per,
                    'wastage_pct' => $item->wastage_pct,
                    'default_warehouse_id' => $item->default_warehouse_id,
                    'default_bin_id' => $item->default_bin_id,
                    'sort' => $item->sort,
                ];
            })->all();

            $this->syncItems($newBom, $items);

            return $newBom;
        });

        return redirect()->route('admin.production.boms.show', $copy)->with('status', __('BOM kopyalandı.'));
    }

    protected function syncItems(Bom $bom, array $items): void
    {
        $bom->items()->delete();

        foreach ($items as $index => $item) {
            BomItem::create([
                'company_id' => $bom->company_id,
                'bom_id' => $bom->id,
                'component_product_id' => $item['component_product_id'],
                'component_variant_id' => $item['component_variant_id'] ?? null,
                'qty_per' => $item['qty_per'],
                'wastage_pct' => $item['wastage_pct'] ?? 0,
                'default_warehouse_id' => $item['default_warehouse_id'] ?? null,
                'default_bin_id' => $item['default_bin_id'] ?? null,
                'sort' => $item['sort'] ?? $index,
            ]);
        }
    }

    protected function nextVersion(int $companyId, int $productId, ?int $variantId): int
    {
        $max = Bom::query()
            ->where('company_id', $companyId)
            ->where('product_id', $productId)
            ->when($variantId, fn (Builder $query) => $query->where('variant_id', $variantId))
            ->max('version');

        return ((int) $max) + 1;
    }
}
