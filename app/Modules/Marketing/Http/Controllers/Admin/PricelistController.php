<?php

namespace App\Modules\Marketing\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\PriceList;
use Illuminate\View\View;

class PricelistController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', PriceList::class);

        $lists = PriceList::query()
            ->withCount('items')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('marketing::admin.pricelists.index', [
            'priceLists' => $lists,
        ]);
    }

    public function show(PriceList $pricelist): View
    {
        $this->authorize('view', $pricelist);

        $pricelist->load(['items' => fn ($query) => $query->with('product')->orderBy('product_id')]);

        return view('marketing::admin.pricelists.show', [
            'pricelist' => $pricelist,
        ]);
    }
}
