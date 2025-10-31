<?php

namespace App\Modules\Finance\Http\Controllers\Admin;

use App\Core\Support\TableKit\Filters;
use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\CashbookEntry;
use App\Modules\Finance\Http\Requests\Admin\CashbookStoreRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CashbookController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CashbookEntry::class);

        $query = CashbookEntry::query()
            ->where('company_id', currentCompanyId())
            ->latest('occurred_at');

        $search = trim((string) $request->query('q', ''));
        $directionFilters = Filters::multi($request, 'direction');
        [$from, $to] = Filters::range($request, 'occurred_at');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('account', 'like', "%{$search}%")
                    ->orWhere('reference_type', 'like', "%{$search}%")
                    ->orWhere('reference_id', 'like', "%{$search}%");
            });
        }

        $normalizedDirections = collect($directionFilters)
            ->filter(fn (string $direction) => in_array($direction, CashbookEntry::directions(), true))
            ->values();

        if ($normalizedDirections->count() === 1) {
            $query->where('direction', $normalizedDirections->first());
        } elseif ($normalizedDirections->count() > 1) {
            $query->whereIn('direction', $normalizedDirections->all());
        }

        if ($from) {
            $query->whereDate('occurred_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('occurred_at', '<=', $to);
        }

        $perPage = (int) $request->integer('perPage', 25);
        $perPage = max(10, min(100, $perPage));

        return view('finance::admin.cashbook.index', [
            'entries' => $query->paginate($perPage)->withQueryString(),
            'directions' => CashbookEntry::directions(),
            'filters' => [
                'q' => $search,
                'direction' => $normalizedDirections->all(),
                'occurred_at' => [
                    'from' => $from,
                    'to' => $to,
                ],
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CashbookEntry::class);

        return view('finance::admin.cashbook.create', [
            'directions' => CashbookEntry::directions(),
        ]);
    }

    public function store(CashbookStoreRequest $request): RedirectResponse
    {
        $companyId = currentCompanyId();
        $entry = CashbookEntry::create(array_merge($request->validated(), [
            'company_id' => $companyId,
        ]));

        return redirect()->route('admin.finance.cashbook.show', $entry)
            ->with('status', __('Cashbook entry recorded.'));
    }

    public function show(CashbookEntry $cashbook): View
    {
        $this->authorize('view', $cashbook);

        return view('finance::admin.cashbook.show', [
            'entry' => $cashbook,
        ]);
    }
}
