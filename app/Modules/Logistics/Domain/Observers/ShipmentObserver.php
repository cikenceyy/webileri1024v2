<?php

namespace App\Modules\Logistics\Domain\Observers;

use App\Core\Bus\Events\ShipmentDelivered;
use App\Core\Bus\Events\ShipmentShipped;
use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Support\Facades\Event;

class ShipmentObserver
{
    public function updating(Shipment $shipment): void
    {
        $shipment->statusWas = $shipment->getOriginal('status');
    }

    public function updated(Shipment $shipment): void
    {
        $previous = $shipment->statusWas ?? null;

        if ($shipment->status === 'shipped' && $previous !== 'shipped') {
            Event::dispatch(new ShipmentShipped($shipment->fresh()));
        }

        if ($shipment->status === 'delivered' && $previous !== 'delivered') {
            Event::dispatch(new ShipmentDelivered($shipment->fresh()));
        }

        unset($shipment->statusWas);
    }
}
