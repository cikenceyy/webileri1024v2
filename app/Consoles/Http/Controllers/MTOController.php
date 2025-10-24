<?php

namespace App\Consoles\Http\Controllers;

use App\Consoles\Domain\MTOConsoleService;
use App\Consoles\Http\Requests\MTOBulkActionRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use LogicException;

class MTOController extends Controller
{
    public function index(MTOConsoleService $service): View
    {
        $this->authorize('viewMTOConsole');

        $companyId = currentCompanyId();
        $state = $service->summary($companyId);

        return view('consoles::admin.mto.index', [
            'state' => $state,
        ]);
    }

    public function action(MTOBulkActionRequest $request, MTOConsoleService $service): RedirectResponse
    {
        $this->authorize('viewMTOConsole');

        $companyId = currentCompanyId();
        $action = $request->validated('action');
        $ids = array_filter($request->validated('ids', []));
        $qty = $request->validated('qty');

        try {
            match ($action) {
                'plan' => $service->planFromSalesLines($companyId, $ids),
                'issue' => $service->issueAllMaterials($companyId, $ids),
                'complete' => $service->completeOrders($companyId, $ids, $qty),
                'close' => $service->closeOrders($companyId, $ids),
                default => null,
            };
        } catch (LogicException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }

        return back()->with('status', __('İşlem tamamlandı.'));
    }
}
