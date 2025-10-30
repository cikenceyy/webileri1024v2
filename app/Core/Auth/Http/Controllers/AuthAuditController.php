<?php

namespace App\Core\Auth\Http\Controllers;

use App\Core\Auth\Models\AuthAudit;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Yetki denemelerini raporlayan salt-okunur yönetim ekranı.
 */
class AuthAuditController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAuthAuditMetrics');

        $query = AuthAudit::query()->latest();
        if ($companyId = currentCompanyId()) {
            $query->where('company_id', $companyId);
        }

        if ($result = $request->query('result')) {
            $query->where('result', $result === 'allowed' ? 'allowed' : 'denied');
        }

        $audits = $query->paginate(25)->withQueryString();

        return view('admin.metrics.auth-audit', [
            'audits' => $audits,
        ]);
    }
}
