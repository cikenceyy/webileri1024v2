<?php

namespace App\Core\Bus\Listeners;

use App\Core\Bus\Events\InvoicePaid;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Finance\Domain\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateCustomerBalanceOnInvoicePaid implements ShouldQueue
{
    use InteractsWithQueue;

    public $afterCommit = true;

    public function handle(InvoicePaid $event): void
    {
        $invoice = $event->invoice->fresh(['customer']);

        if (! $invoice || ! $invoice->customer instanceof Customer) {
            return;
        }

        $customer = $invoice->customer;

        if (class_exists(Invoice::class)) {
            $balance = Invoice::query()
                ->where('company_id', $invoice->company_id)
                ->where('customer_id', $customer->getKey())
                ->sum('balance_due');
        } else {
            $balance = 0.0;
        }

        $customer->forceFill(['balance' => round((float) $balance, 2)])->save();
    }
}
