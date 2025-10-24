<?php

namespace App\Modules\Marketing\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\PriceList;
use App\Modules\Marketing\Domain\PricelistBulkUpdater;
use App\Modules\Marketing\Http\Requests\Admin\PricelistBulkApplyRequest;
use App\Modules\Marketing\Http\Requests\Admin\PricelistBulkPreviewRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PricelistBulkController extends Controller
{
    public function __construct(private readonly PricelistBulkUpdater $updater)
    {
    }

    public function form(PriceList $pricelist): View
    {
        $this->abortIfDisabled();
        $this->authorize('bulkUpdate', $pricelist);

        return view('marketing::admin.pricelists.bulk.form', [
            'pricelist' => $pricelist,
        ]);
    }

    public function preview(PricelistBulkPreviewRequest $request, PriceList $pricelist): View
    {
        $this->abortIfDisabled();
        $this->authorize('bulkUpdate', $pricelist);

        $filters = $request->validated();
        $operation = $filters['operation'];
        unset($filters['operation']);

        $changes = $this->updater->preview($pricelist, $filters, $operation);

        return view('marketing::admin.pricelists.bulk.preview', [
            'pricelist' => $pricelist,
            'filters' => $filters,
            'operation' => $operation,
            'changes' => $changes,
        ]);
    }

    public function apply(PricelistBulkApplyRequest $request, PriceList $pricelist): RedirectResponse
    {
        $this->abortIfDisabled();
        $this->authorize('bulkUpdate', $pricelist);

        $filters = $request->validated();
        $operation = $filters['operation'];
        unset($filters['operation']);

        $changes = $this->updater->apply($pricelist, $filters, $operation);

        return redirect()
            ->route('admin.marketing.pricelists.show', $pricelist)
            ->with('status', __(':count fiyat güncellendi.', ['count' => $changes->count()]));
    }

    protected function abortIfDisabled(): void
    {
        if (! config('features.marketing.pricelists_bulk_update', true)) {
            abort(404);
        }
    }
}
