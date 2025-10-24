<?php

namespace App\Consoles\Http\Controllers;

use App\Consoles\Domain\ReplenishConsoleService;
use App\Consoles\Http\Requests\ReplenishFilterRequest;
use App\Consoles\Http\Requests\ReplenishTransferRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use LogicException;

class ReplenishController extends Controller
{
    public function index(ReplenishFilterRequest $request, ReplenishConsoleService $service): View
    {
        $this->authorize('viewReplenishConsole');

        $state = $service->summary(currentCompanyId(), $request->validated());

        return view('consoles::admin.replenish.index', [
            'state' => $state,
        ]);
    }

    public function createTransfer(ReplenishTransferRequest $request, ReplenishConsoleService $service): RedirectResponse
    {
        $this->authorize('viewReplenishConsole');

        $companyId = currentCompanyId();
        $data = $request->validated();

        try {
            $service->createTransfer(
                $companyId,
                (int) $data['from_warehouse_id'],
                (int) $data['to_warehouse_id'],
                $data['lines']
            );
        } catch (LogicException $e) {
            return back()->withErrors(['transfer' => $e->getMessage()]);
        }

        return back()->with('status', __('Transfer oluşturulup gönderildi.'));
    }
}
