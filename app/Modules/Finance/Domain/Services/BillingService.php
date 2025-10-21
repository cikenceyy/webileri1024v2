<?php

namespace App\Modules\Finance\Domain\Services;

use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\InvoiceLine;
use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function fromOrder(Order $order): Invoice
    {
        $order->loadMissing(['lines', 'customer']);

        return DB::transaction(function () use ($order): Invoice {
            $companyId = $order->company_id;
            $shippingTotal = 0.0;

            if (class_exists(Shipment::class)) {
                $shippingTotal = (float) Shipment::query()
                    ->where('company_id', $companyId)
                    ->where('order_id', $order->getKey())
                    ->sum('shipping_cost');
            }

            $invoice = Invoice::create([
                'company_id' => $companyId,
                'customer_id' => $order->customer_id,
                'order_id' => $order->getKey(),
                'invoice_no' => Invoice::generateInvoiceNo($companyId),
                'issue_date' => now()->toDateString(),
                'due_date' => $order->due_date ?? now()->addDays(14)->toDateString(),
                'currency' => $order->currency,
                'status' => 'draft',
                'notes' => $order->notes,
                'created_by' => optional(auth()->user())->getKey(),
                'shipping_total' => round($shippingTotal, 2),
            ]);

            foreach ($order->lines as $index => $line) {
                InvoiceLine::create([
                    'company_id' => $companyId,
                    'invoice_id' => $invoice->getKey(),
                    'product_id' => $line->product_id,
                    'variant_id' => $line->variant_id,
                    'description' => $line->description,
                    'qty' => $line->qty,
                    'unit' => $line->unit,
                    'unit_price' => $line->unit_price,
                    'discount_rate' => $line->discount_rate,
                    'tax_rate' => $line->tax_rate,
                    'line_total' => $line->line_total,
                    'sort_order' => $index,
                ]);
            }

            $invoice->load(['lines', 'allocations']);
            $invoice->refreshTotals();
            $invoice->save();

            return $invoice->fresh(['customer', 'lines']);
        });
    }
}
