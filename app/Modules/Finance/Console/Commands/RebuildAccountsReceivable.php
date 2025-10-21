<?php

namespace App\Modules\Finance\Console\Commands;

use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildAccountsReceivable extends Command
{
    protected $signature = 'ar:rebuild {--company=}';

    protected $description = 'Recalculate invoice balances, receipt allocations and customer balances.';

    public function handle(): int
    {
        $companyId = $this->option('company') ? (int) $this->option('company') : null;

        DB::transaction(function () use ($companyId): void {
            $this->info('Updating invoices…');
            Invoice::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->with(['lines', 'allocations'])
                ->chunkById(200, function ($invoices): void {
                    foreach ($invoices as $invoice) {
                        $invoice->refreshTotals();
                        $invoice->save();
                    }
                });

            $this->info('Updating receipts…');
            Receipt::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->with('allocations')
                ->chunkById(200, function ($receipts): void {
                    foreach ($receipts as $receipt) {
                        $receipt->refreshAllocatedTotal();
                    }
                });

            $this->info('Updating customer balances…');
            $balanceQuery = Invoice::query()
                ->selectRaw('customer_id, SUM(balance_due) as balance')
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->groupBy('customer_id')
                ->pluck('balance', 'customer_id');

            $customerQuery = Customer::query()->when($companyId, fn ($q) => $q->where('company_id', $companyId));
            $customerQuery->update(['balance' => 0]);

            foreach ($balanceQuery as $customerId => $balance) {
                Customer::where('id', $customerId)->update(['balance' => round((float) $balance, 2)]);
            }
        });

        $this->info('Accounts receivable rebuilt successfully.');

        return self::SUCCESS;
    }
}
