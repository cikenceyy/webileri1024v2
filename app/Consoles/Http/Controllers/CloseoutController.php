<?php

namespace App\Consoles\Http\Controllers;

use App\Consoles\Domain\CloseoutConsoleService;
use App\Consoles\Http\Requests\CloseoutPrintRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CloseoutController extends Controller
{
    public function index(Request $request, CloseoutConsoleService $service): View
    {
        $this->authorize('viewCloseoutConsole');

        $companyId = currentCompanyId();
        $state = $service->summary($companyId, $request->input('date'));

        return view('consoles::admin.closeout.index', [
            'state' => $state,
        ]);
    }

    public function batchPrint(CloseoutPrintRequest $request, CloseoutConsoleService $service): View
    {
        $this->authorize('viewCloseoutConsole');

        $links = $service->batchPrint($request->validated('selection'));

        return view('consoles::admin.closeout.print', [
            'links' => $links,
        ]);
    }
}
