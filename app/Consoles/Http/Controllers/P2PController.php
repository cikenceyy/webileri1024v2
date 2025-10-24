<?php

namespace App\Consoles\Http\Controllers;

use App\Consoles\Domain\P2PConsoleService;
use App\Consoles\Http\Requests\P2PBulkActionRequest;
use App\Consoles\Http\Requests\P2PFilterRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use LogicException;

class P2PController extends Controller
{
    public function index(P2PFilterRequest $request, P2PConsoleService $service): View
    {
        $this->authorize('viewP2PConsole');

        $companyId = currentCompanyId();
        $state = $service->summary($companyId, $request->validated());

        return view('consoles::admin.p2p.index', [
            'state' => $state,
        ]);
    }

    public function action(P2PBulkActionRequest $request, P2PConsoleService $service): RedirectResponse
    {
        $this->authorize('viewP2PConsole');

        $companyId = currentCompanyId();
        $action = $request->validated('action');
        $ids = array_filter($request->validated('ids', []));
        $warehouseId = $request->integer('warehouse_id');
        $reason = $request->validated('reason');

        try {
            match ($action) {
                'approve_pos' => $service->approvePurchaseOrders($companyId, $ids),
                'po_to_grn' => $service->createReceiptsFromPurchaseOrders($companyId, $ids, $warehouseId),
                'receive_grn' => $service->receiveReceipts($companyId, $ids, $warehouseId),
                'reconcile_grn' => $service->reconcileReceipts($companyId, $ids, $reason),
                default => null,
            };
        } catch (LogicException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }

        return back()->with('status', __('İşlem tamamlandı.'));
    }
}
