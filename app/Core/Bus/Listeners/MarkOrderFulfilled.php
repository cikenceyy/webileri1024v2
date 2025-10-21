<?php

namespace App\Core\Bus\Listeners;

use App\Core\Bus\Events\ShipmentDelivered;
use App\Modules\Marketing\Domain\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MarkOrderFulfilled implements ShouldQueue
{
    use InteractsWithQueue;

    public $afterCommit = true;

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

        if ($order->status === 'shipped') {
            return;
        }

        $order->forceFill(['status' => 'shipped'])->save();
    }
}
