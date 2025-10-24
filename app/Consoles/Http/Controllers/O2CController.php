<?php

namespace App\Consoles\Http\Controllers;

use App\Consoles\Domain\O2CConsoleService;
use App\Consoles\Http\Requests\O2CBulkActionRequest;
use App\Consoles\Http\Requests\O2CFilterRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use LogicException;

class O2CController extends Controller
{
    public function index(O2CFilterRequest $request, O2CConsoleService $service): View
    {
        $this->authorize('viewO2CConsole');

        $companyId = currentCompanyId();
        $filters = $request->validated();
        $state = $service->summary($companyId, $filters);

        return view('consoles::admin.o2c.index', [
            'state' => $state,
        ]);
    }

    public function action(O2CBulkActionRequest $request, O2CConsoleService $service): RedirectResponse
    {
        $this->authorize('viewO2CConsole');

        $companyId = currentCompanyId();
        $action = $request->validated('action');
        $ids = array_filter($request->validated('ids', []));
        $warehouseId = $request->integer('warehouse_id') ?: null;

        try {
            match ($action) {
                'orders_to_shipments' => $service->createShipments($companyId, $ids, ['warehouse_id' => $warehouseId]),
                'shipments_pick' => $service->pickShipments($companyId, $ids),
                'shipments_pack' => $service->packShipments($companyId, $ids),
                'shipments_ship' => $service->shipShipments($companyId, $ids),
                'orders_to_invoices' => $service->createInvoices($companyId, $ids),
                'invoices_apply_receipt' => $service->applyReceipts($companyId, $ids),
                default => null,
            };
        } catch (LogicException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }

        return back()->with('status', __('İşlem tamamlandı.'));
    }
}
