<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class BankTransactionController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.finance.cash-panel.index');
    }
}
