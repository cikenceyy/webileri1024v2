<?php

namespace App\Core\Bus\Listeners;

use App\Core\Bus\Events\GrnReceived;
use App\Modules\Finance\Domain\Models\ApInvoice;
use App\Modules\Finance\Domain\Models\ApInvoiceLine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class CreateApInvoiceFromGrn implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public bool $afterCommit = true;

    public function handle(GrnReceived $event): void
    {
        $grn = $event->grn->fresh(['purchaseOrder', 'purchaseOrder.lines', 'lines.poLine']);

        if (! $grn || $grn->lines->isEmpty() || ! $grn->purchaseOrder) {
            return;
        }

        if (ApInvoice::query()->where('grn_id', $grn->id)->exists()) {
            return;
        }

        DB::transaction(function () use ($grn): void {
            $purchaseOrder = $grn->purchaseOrder;

            $invoice = ApInvoice::query()->create([
                'purchase_order_id' => $purchaseOrder->id,
                'grn_id' => $grn->id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'status' => 'draft',
                'currency' => $purchaseOrder->currency,
                'expected_total' => 0,
                'subtotal' => 0,
                'tax_total' => 0,
                'total' => 0,
                'balance_due' => 0,
            ]);

            $expectedTotal = 0;

            foreach ($grn->lines as $grnLine) {
                $poLine = $grnLine->poLine;
                $unitPrice = (float) ($poLine?->unit_price ?? 0);
                $qtyReceived = (float) $grnLine->qty_received;
                $lineAmount = round($qtyReceived * $unitPrice, 2);

                $expectedTotal += $lineAmount;

                ApInvoiceLine::query()->create([
                    'ap_invoice_id' => $invoice->id,
                    'description' => $poLine?->description ?? 'Mal kabul satırı',
                    'qty' => $qtyReceived,
                    'unit' => $poLine?->unit ?? 'adet',
                    'unit_price' => $unitPrice,
                    'amount' => $lineAmount,
                    'source_type' => 'grn_line',
                    'source_uuid' => $grnLine->uuid,
                ]);
            }

            $invoice->fill([
                'expected_total' => round($expectedTotal, 2),
            ]);

            $invoice->refreshTotals();
            $invoice->save();
        });
    }
}
