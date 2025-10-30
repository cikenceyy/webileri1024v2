<?php

namespace App\Modules\Settings\Http\Controllers\Admin;

use App\Core\Tenancy\DomainDiagnostics;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Domain çözümleme teşhis panelini yalnızca okunur şekilde sunar.
 */
class DiagnosticsController extends Controller
{
    public function __construct(private readonly DomainDiagnostics $diagnostics)
    {
    }

    public function index(Request $request): View
    {
        return view('settings::admin.diagnostics', [
            'data' => $this->diagnostics->forRequest($request),
        ]);
    }
}
