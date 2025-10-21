<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\BankAccount;
use App\Modules\Finance\Domain\Models\BankTransaction;
use App\Modules\Finance\Http\Requests\StoreBankTransactionRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BankTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', BankAccount::class);

        $query = BankTransaction::query()->with('bankAccount');

        if ($accountId = $request->integer('bank_account_id')) {
            $query->where('bank_account_id', $accountId);
        }

        $transactions = $query->latest('transacted_at')->paginate(25)->withQueryString();

        return view('finance::banks.transactions', [
            'transactions' => $transactions,
            'accounts' => BankAccount::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['bank_account_id']),
        ]);
    }

    public function store(StoreBankTransactionRequest $request): RedirectResponse
    {
        $this->authorize('create', BankAccount::class);

        $transaction = BankTransaction::create($request->validated());

        return redirect()->route('admin.finance.bank-transactions.index')->with('status', __('Bank transaction recorded.'));
    }

    public function destroy(BankTransaction $bankTransaction): RedirectResponse
    {
        $this->authorize('delete', $bankTransaction->bankAccount);

        $bankTransaction->delete();

        return redirect()->route('admin.finance.bank-transactions.index')->with('status', __('Bank transaction deleted.'));
    }
}
