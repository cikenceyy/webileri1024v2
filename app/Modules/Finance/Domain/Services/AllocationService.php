<?php

namespace App\Modules\Finance\Domain\Services;

use App\Modules\Finance\Domain\Models\Allocation;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Core\Bus\Events\InvoicePaid;
use App\Modules\Finance\Domain\Models\Receipt;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AllocationService
{
    public function allocate(Receipt $receipt, Invoice $invoice, float $amount): Allocation
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Allocation amount must be positive.');
        }

        return DB::transaction(function () use ($receipt, $invoice, $amount): Allocation {
            $availableReceipt = $receipt->amount - $receipt->allocated_total;
            $invoiceBalance = $invoice->balance_due;

            if ($amount > $availableReceipt + 0.01) {
                throw new InvalidArgumentException('Allocation exceeds receipt availability.');
            }

            if ($amount > $invoiceBalance + 0.01) {
                throw new InvalidArgumentException('Allocation exceeds invoice balance.');
            }

            $allocation = Allocation::create([
                'company_id' => $invoice->company_id,
                'invoice_id' => $invoice->getKey(),
                'receipt_id' => $receipt->getKey(),
                'amount' => round($amount, 2),
                'allocated_at' => now(),
                'allocated_by' => optional(auth()->user())->getKey(),
            ]);

            $invoice->load('lines', 'allocations');
            $invoice->refreshTotals();
            if ($invoice->balance_due <= 0.01) {
                $invoice->status = 'paid';
            }
            $invoice->save();

            if ($invoiceBalance > 0.01 && $invoice->balance_due <= 0.01) {
                event(new InvoicePaid($invoice->fresh(['customer'])));
            }

            $receipt->refreshAllocatedTotal();

            return $allocation;
        });
    }
}
