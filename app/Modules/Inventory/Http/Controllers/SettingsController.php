<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\ProductCategory;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use App\Modules\Inventory\Domain\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ProductCategory::class);
        $this->authorize('viewAny', Unit::class);

        $categories = ProductCategory::query()
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        $variantSets = ProductVariant::query()
            ->orderBy('sku')
            ->limit(50)
            ->get(['id', 'product_id', 'sku', 'barcode']);

        $units = Unit::query()
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'is_base', 'to_base']);

        return view('inventory::settings.index', [
            'categories' => $categories,
            'variantSets' => $variantSets,
            'units' => $units,
        ]);
    }
}
