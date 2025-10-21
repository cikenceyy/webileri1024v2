<?php

namespace App\Consoles\Http\Controllers;

use App\Consoles\Domain\Dto\O2CQuery;
use App\Consoles\Http\Requests\O2CActionRequest;
use App\Consoles\Http\Requests\O2CQueryRequest;
use App\Core\Orchestrations\OrderToCashOrchestration;
use App\Http\Controllers\Controller;
use App\Modules\Marketing\Domain\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class O2CController extends Controller
{
    public function index(O2CQueryRequest $request, OrderToCashOrchestration $orchestration): View
    {
        $this->authorize('viewAny', Order::class);

        $query = O2CQuery::fromArray($request->validated());
        $state = $orchestration->preview($query->toArray());

        return view('consoles::o2c', [
            'state' => $state,
            'filters' => $query->toArray(),
            'module' => 'Consoles',
            'page' => 'o2c',
        ]);
    }

    public function execute(O2CActionRequest $request, string $step, OrderToCashOrchestration $orchestration): RedirectResponse
    {
        $result = $orchestration->executeStep(
            $step,
            $request->validated(),
            $request->header('X-Idempotency-Key')
        );

        return back()->with($result->success ? 'success' : 'error', $result->message);
    }
}
