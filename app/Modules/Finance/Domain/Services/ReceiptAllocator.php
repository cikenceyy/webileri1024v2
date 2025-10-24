<?php

namespace App\Modules\Finance\Domain\Services;

use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use App\Modules\Finance\Domain\Models\ReceiptApplication;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReceiptAllocator
{
    /**
     * @param array<int, array{invoice_id:int, amount:float|string}> $rows
     */
    public function apply(Receipt $receipt, array $rows): void
    {
        DB::transaction(function () use ($receipt, $rows): void {
            $companyId = (int) $receipt->company_id;
            $receipt->load('applications.invoice');

            foreach ($receipt->applications as $application) {
                $application->invoice?->revertPayment((float) $application->amount);
                $application->delete();
            }

            $total = 0.0;

            foreach ($rows as $row) {
                $amount = round((float) Arr::get($row, 'amount', 0), 2);
                $invoiceId = (int) Arr::get($row, 'invoice_id');

                if ($amount <= 0) {
                    continue;
                }

                /** @var Invoice|null $invoice */
                $invoice = Invoice::where('company_id', $companyId)->find($invoiceId);
                if (! $invoice) {
                    throw new InvalidArgumentException('Invoice not found for allocation.');
                }

                if (! $invoice->isIssued()) {
                    throw new InvalidArgumentException('Invoice is not open for allocation.');
                }

                $remaining = (float) $invoice->grand_total - (float) $invoice->paid_amount;
                if ($amount - $remaining > 0.01) {
                    throw new InvalidArgumentException('Allocation exceeds invoice balance.');
                }

                $total += $amount;

                ReceiptApplication::create([
                    'company_id' => $companyId,
                    'receipt_id' => $receipt->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $amount,
                ]);

                $invoice->applyPayment($amount);
            }

            if ($total - (float) $receipt->amount > 0.01) {
                throw new InvalidArgumentException('Allocation exceeds receipt amount.');
            }
        });
    }
}
