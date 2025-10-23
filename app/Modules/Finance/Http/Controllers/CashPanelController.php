<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\BankAccount;
use App\Modules\Finance\Domain\Models\BankTransaction;
use App\Modules\Finance\Http\Requests\StoreBankAccountRequest;
use App\Modules\Finance\Http\Requests\StoreBankTransactionRequest;
use App\Modules\Finance\Http\Requests\UpdateBankAccountRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CashPanelController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', BankAccount::class);

        $currency = $request->string('currency')->toString();

        $accounts = BankAccount::query()
            ->select(['id', 'name', 'account_no', 'currency', 'is_default', 'status'])
            ->selectRaw("(
                SELECT COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE -amount END), 0)
                FROM bank_transactions
                WHERE bank_transactions.bank_account_id = bank_accounts.id
            ) as balance")
            ->selectRaw("(
                SELECT MAX(transacted_at)
                FROM bank_transactions
                WHERE bank_transactions.bank_account_id = bank_accounts.id
            ) as last_transaction_at")
            ->when($currency !== '', fn ($query) => $query->where('currency', $currency))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $transactions = BankTransaction::query()
            ->with('bankAccount:id,name,currency')
            ->when($currency !== '', function ($query) use ($currency): void {
                $query->whereHas('bankAccount', function ($accountQuery) use ($currency): void {
                    $accountQuery->where('currency', $currency);
                });
            })
            ->latest('transacted_at')
            ->limit(30)
            ->get();

        $currencies = BankAccount::query()
            ->select('currency')
            ->distinct()
            ->orderBy('currency')
            ->pluck('currency');

        $netBalance = (float) BankTransaction::query()
            ->when($currency !== '', function ($query) use ($currency): void {
                $query->where('currency', $currency);
            })
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE -amount END), 0) as net")
            ->value('net');

        return view('finance::banks.panel', [
            'accounts' => $accounts,
            'transactions' => $transactions,
            'currencies' => $currencies,
            'activeCurrency' => $currency,
            'netBalance' => $netBalance,
        ]);
    }

    public function storeAccount(StoreBankAccountRequest $request): RedirectResponse
    {
        $this->authorize('create', BankAccount::class);

        $account = BankAccount::create($request->validated());

        if ($account->is_default) {
            $this->unsetOtherDefaults($account);
        }

        return redirect()->route('admin.finance.cash-panel.index')->with('status', __('Bank account created.'));
    }

    public function updateAccount(UpdateBankAccountRequest $request, BankAccount $account): RedirectResponse
    {
        $this->authorize('update', $account);

        $account->update($request->validated());

        if ($account->is_default) {
            $this->unsetOtherDefaults($account);
        }

        return redirect()->route('admin.finance.cash-panel.index')->with('status', __('Bank account updated.'));
    }

    public function destroyAccount(BankAccount $account): RedirectResponse
    {
        $this->authorize('delete', $account);

        $account->delete();

        return redirect()->route('admin.finance.cash-panel.index')->with('status', __('Bank account deleted.'));
    }

    public function storeTransaction(StoreBankTransactionRequest $request): RedirectResponse
    {
        $this->authorize('create', BankAccount::class);

        BankTransaction::create($request->validated());

        return redirect()->route('admin.finance.cash-panel.index')->with('status', __('Bank transaction recorded.'));
    }

    public function destroyTransaction(BankTransaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction->bankAccount);

        $transaction->delete();

        return redirect()->route('admin.finance.cash-panel.index')->with('status', __('Bank transaction deleted.'));
    }

    public function importTransactions(Request $request): RedirectResponse
    {
        $this->authorize('create', BankAccount::class);

        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $data['file'];
        $path = $file->getRealPath();
        $handle = fopen($path, 'rb');

        if (! $handle) {
            throw ValidationException::withMessages([
                'file' => __('Unable to read the uploaded file.'),
            ]);
        }

        $header = null;
        $imported = 0;

        DB::transaction(function () use (&$handle, &$header, &$imported): void {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if ($header === null) {
                    $header = array_map('trim', $row);
                    continue;
                }

                $payload = $this->mapCsvRow($header, $row);

                if (! $payload) {
                    continue;
                }

                BankTransaction::create($payload);
                $imported++;
            }
        });

        fclose($handle);

        $message = match (true) {
            $imported === 0 => __('No transactions imported.'),
            $imported === 1 => __('1 transaction imported.'),
            default => __(':count transactions imported.', ['count' => $imported]),
        };

        return redirect()->route('admin.finance.cash-panel.index')->with('status', $message);
    }

    protected function unsetOtherDefaults(BankAccount $account): void
    {
        BankAccount::where('company_id', $account->company_id)
            ->whereKeyNot($account->getKey())
            ->update(['is_default' => false]);
    }

    protected function mapCsvRow(array $header, array $row): ?array
    {
        $data = [];

        foreach ($header as $index => $key) {
            $data[Str::snake($key)] = $row[$index] ?? null;
        }

        if (empty($data['bank_account_id']) || empty($data['type']) || empty($data['amount']) || empty($data['currency'])) {
            return null;
        }

        $type = strtolower((string) $data['type']);

        if (! in_array($type, ['deposit', 'withdrawal'], true)) {
            return null;
        }

        $currency = strtoupper((string) $data['currency']);

        if (! in_array($currency, config('finance.supported_currencies', []), true)) {
            return null;
        }

        $transactedAt = now()->toDateString();
        if (! empty($data['transacted_at'])) {
            try {
                $transactedAt = Carbon::parse($data['transacted_at'])->toDateString();
            } catch (Throwable $exception) {
                $transactedAt = now()->toDateString();
            }
        }

        return [
            'bank_account_id' => (int) $data['bank_account_id'],
            'type' => $type,
            'amount' => (float) $data['amount'],
            'currency' => $currency,
            'transacted_at' => $transactedAt,
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }
}
