<?php

namespace App\Modules\Logistics\Domain\Events;

use App\Modules\Logistics\Domain\Models\Shipment;

class ShipmentShipped
{
    public function __construct(public Shipment $shipment)
    {
    }
}
