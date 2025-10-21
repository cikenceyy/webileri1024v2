<?php

namespace App\Consoles\Http\Controllers;

use App\Consoles\Domain\Dto\P2PQuery;
use App\Consoles\Http\Requests\P2PActionRequest;
use App\Consoles\Http\Requests\P2PQueryRequest;
use App\Core\Orchestrations\ProcureToPayOrchestration;
use App\Http\Controllers\Controller;
use App\Modules\Procurement\Domain\Models\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class P2PController extends Controller
{
    public function index(P2PQueryRequest $request, ProcureToPayOrchestration $orchestration): View
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $query = P2PQuery::fromArray($request->validated());
        $state = $orchestration->preview($query->toArray());

        return view('consoles.p2p', [
            'state' => $state,
            'filters' => $query->toArray(),
            'module' => 'Consoles',
            'page' => 'p2p',
        ]);
    }

    public function execute(P2PActionRequest $request, string $step, ProcureToPayOrchestration $orchestration): RedirectResponse
    {
        $result = $orchestration->executeStep(
            $step,
            $request->validated(),
            $request->header('X-Idempotency-Key')
        );

        return back()->with($result->success ? 'success' : 'error', $result->message);
    }
}
