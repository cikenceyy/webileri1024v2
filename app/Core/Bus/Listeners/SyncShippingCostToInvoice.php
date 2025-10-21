<?php

namespace App\Core\Bus\Listeners;

use App\Core\Bus\Events\ShipmentDelivered;
use App\Core\Bus\Events\ShipmentShipped;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncShippingCostToInvoice implements ShouldQueue
{
    use InteractsWithQueue;

    public $afterCommit = true;

    public function handle(ShipmentShipped|ShipmentDelivered $event): void
    {
        if (! class_exists(Invoice::class)) {
            return;
        }

        $shipment = $event->shipment->fresh();

        if (! $shipment || ! $shipment->order_id) {
            return;
        }

        $invoice = Invoice::query()
            ->where('company_id', $shipment->company_id)
            ->where('order_id', $shipment->order_id)
            ->latest('issue_date')
            ->first();

        if (! $invoice) {
            return;
        }

        $totalShipping = Shipment::query()
            ->where('company_id', $shipment->company_id)
            ->where('order_id', $shipment->order_id)
            ->sum('shipping_cost');

        $invoice->forceFill(['shipping_total' => round((float) $totalShipping, 2)]);
        $invoice->load('lines', 'allocations');
        $invoice->refreshTotals();
        $invoice->save();
    }
}
