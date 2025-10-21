<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\BankAccount;
use App\Modules\Finance\Http\Requests\StoreBankAccountRequest;
use App\Modules\Finance\Http\Requests\UpdateBankAccountRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BankAccountController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', BankAccount::class);

        $accounts = BankAccount::orderBy('name')->paginate(20);

        return view('finance::banks.index', [
            'accounts' => $accounts,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', BankAccount::class);

        return view('finance::banks.create', [
            'currencies' => config('finance.supported_currencies'),
        ]);
    }

    public function store(StoreBankAccountRequest $request): RedirectResponse
    {
        $this->authorize('create', BankAccount::class);

        $data = $request->validated();
        $account = BankAccount::create($data);

        if ($account->is_default) {
            BankAccount::where('company_id', $account->company_id)
                ->whereKeyNot($account->getKey())
                ->update(['is_default' => false]);
        }

        return redirect()->route('admin.finance.bank-accounts.index')->with('status', __('Bank account created.'));
    }

    public function edit(BankAccount $bankAccount): View
    {
        $this->authorize('update', $bankAccount);

        return view('finance::banks.edit', [
            'account' => $bankAccount,
            'currencies' => config('finance.supported_currencies'),
        ]);
    }

    public function update(UpdateBankAccountRequest $request, BankAccount $bankAccount): RedirectResponse
    {
        $this->authorize('update', $bankAccount);

        $bankAccount->update($request->validated());

        if ($bankAccount->is_default) {
            BankAccount::where('company_id', $bankAccount->company_id)
                ->whereKeyNot($bankAccount->getKey())
                ->update(['is_default' => false]);
        }

        return redirect()->route('admin.finance.bank-accounts.index')->with('status', __('Bank account updated.'));
    }

    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        $this->authorize('delete', $bankAccount);

        $bankAccount->delete();

        return redirect()->route('admin.finance.bank-accounts.index')->with('status', __('Bank account deleted.'));
    }
}
