<?php

namespace App\Modules\Marketing\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\PriceList;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricelistController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PriceList::class);

        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'all');

        $query = PriceList::query()->withCount('items');

        if ($search !== '') {
            $query->search($search);
        }

        if ($status === 'active') {
            $query->where('active', true);
        } elseif ($status === 'inactive') {
            $query->where('active', false);
        }

        $lists = $query
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('marketing::admin.pricelists.index', [
            'priceLists' => $lists,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
            'stats' => $this->stats(),
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

    protected function stats(): array
    {
        $base = PriceList::query();

        $total = (clone $base)->count();
        $active = (clone $base)->where('active', true)->count();
        $inactive = (clone $base)->where('active', false)->count();
        $avgItems = (clone $base)->withCount('items')->get()->avg('items_count') ?? 0;

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'averageItems' => round($avgItems, 1),
        ];
    }
}
