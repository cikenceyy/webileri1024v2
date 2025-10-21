<?php

namespace App\Core\Bus\Events;

use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShipmentShipped
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Shipment $shipment)
    {
    }
}
