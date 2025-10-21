<?php

namespace App\Core\Bus\Listeners;

use App\Core\Bus\Events\ShipmentDelivered;
use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Services\BillingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateInvoiceFromShipment implements ShouldQueue
{
    use InteractsWithQueue;

    public $afterCommit = true;

    public function __construct(private BillingService $billingService)
    {
    }

    public function handle(ShipmentDelivered $event): void
    {
        $shipment = $event->shipment->fresh(['order']);

        if (! $shipment || ! $shipment->order) {
            return;
        }

        $order = $shipment->order;

        if (! $order instanceof Order) {
            return;
        }

        $alreadyInvoiced = Invoice::withTrashed()
            ->where('company_id', $order->company_id)
            ->where('order_id', $order->getKey())
            ->exists();

        if ($alreadyInvoiced) {
            return;
        }

        $this->billingService->fromOrder($order);
    }
}
