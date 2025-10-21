<?php

namespace App\Consoles\Http\Controllers;

use App\Consoles\Domain\Dto\MTOQuery;
use App\Consoles\Http\Requests\MTOActionRequest;
use App\Consoles\Http\Requests\MTOQueryRequest;
use App\Core\Orchestrations\MakeToOrderOrchestration;
use App\Http\Controllers\Controller;
use App\Modules\Production\Domain\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MTOController extends Controller
{
    public function index(MTOQueryRequest $request, MakeToOrderOrchestration $orchestration): View
    {
        $this->authorize('viewAny', WorkOrder::class);

        $query = MTOQuery::fromArray($request->validated());
        $state = $orchestration->preview($query->toArray());

        return view('consoles.mto', [
            'state' => $state,
            'filters' => $query->toArray(),
            'module' => 'Consoles',
            'page' => 'mto',
        ]);
    }

    public function execute(MTOActionRequest $request, string $step, MakeToOrderOrchestration $orchestration): RedirectResponse
    {
        $result = $orchestration->executeStep(
            $step,
            $request->validated(),
            $request->header('X-Idempotency-Key')
        );

        return back()->with($result->success ? 'success' : 'error', $result->message);
    }
}
