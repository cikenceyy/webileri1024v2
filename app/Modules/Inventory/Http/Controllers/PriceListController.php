<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\PriceList;
use App\Modules\Inventory\Domain\Models\PriceListItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricelistController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PriceList::class);

        $lists = PriceList::query()
            ->withCount('items')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('inventory::pricelists.index', [
            'priceLists' => $lists,
        ]);
    }

    public function show(PriceList $pricelist): View
    {
        $this->authorize('view', $pricelist);

        $pricelist->load(['items' => fn ($query) => $query->with('product')->orderBy('product_id')]);

        return view('inventory::pricelists.show', [
            'pricelist' => $pricelist,
        ]);
    }
}
