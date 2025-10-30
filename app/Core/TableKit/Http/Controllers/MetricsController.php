<?php

namespace App\Core\TableKit\Http\Controllers;

use App\Core\TableKit\Models\TablekitMetricDaily;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Throwable;

/**
 * TableKit performans metriklerini yöneticilere gösteren controller.
 */
class MetricsController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = currentCompanyId();
        $dateInput = $request->string('date')->toString();

        try {
            $date = $dateInput !== ''
                ? CarbonImmutable::parse($dateInput)
                : CarbonImmutable::yesterday();
        } catch (Throwable) {
            $date = CarbonImmutable::yesterday();
        }

        $date = $date->startOfDay();
        $tableKey = $request->string('table_key')->toString();

        $query = TablekitMetricDaily::query()
            ->where('company_id', $companyId)
            ->where('date', $date->toDateString());

        if ($tableKey !== '') {
            $query->where('table_key', $tableKey);
        }

        $entries = $query->orderByDesc('request_count')->get();

        $topTables = $entries->sortByDesc('request_count')->take(5);

        return view('admin.tablekit.metrics', [
            'entries' => $entries,
            'selectedDate' => $date,
            'selectedTableKey' => $tableKey,
            'topTables' => $topTables,
        ]);
    }
}
