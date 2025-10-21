<?php

namespace App\Consoles\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TodayBoardController
{
    public function index(Request $request): View
    {
        $summary = [
            'orders' => $request->user()?->open_orders_count ?? 0,
            'shipments' => $request->user()?->open_shipments_count ?? 0,
        ];

        return view('consoles::today', [
            'summary' => $summary,
        ]);
    }
}
