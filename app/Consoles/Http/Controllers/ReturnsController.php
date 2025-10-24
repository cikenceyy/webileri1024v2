<?php

namespace App\Consoles\Http\Controllers;

use App\Consoles\Domain\ReturnsConsoleService;
use App\Consoles\Http\Requests\ReturnsActionRequest;
use App\Consoles\Http\Requests\ReturnsFilterRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReturnsController extends Controller
{
    public function index(ReturnsFilterRequest $request, ReturnsConsoleService $service): View
    {
        $this->authorize('viewReturnsConsole');

        $state = $service->summary(currentCompanyId(), $request->validated());

        return view('consoles::admin.returns.index', [
            'state' => $state,
        ]);
    }

    public function action(ReturnsActionRequest $request, ReturnsConsoleService $service): RedirectResponse
    {
        $this->authorize('viewReturnsConsole');

        $companyId = currentCompanyId();
        $action = $request->validated('action');
        $ids = array_filter($request->validated('ids', []));

        match ($action) {
            'approve' => $service->approve($companyId, $ids),
            'close' => $service->close($companyId, $ids),
            'create_receipt' => $service->createReceipts($companyId, $ids),
            default => null,
        };

        return back()->with('status', __('İşlem tamamlandı.'));
    }
}
