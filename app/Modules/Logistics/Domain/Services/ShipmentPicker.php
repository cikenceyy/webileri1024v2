<?php

namespace App\Modules\Logistics\Domain\Services;

use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LogicException;

class ShipmentPicker
{
    public function pick(Shipment $shipment, array $linePayloads): void
    {
        if (! in_array($shipment->status, ['draft', 'picking'], true)) {
            throw new LogicException('Shipment cannot be picked in its current status.');
        }

        DB::transaction(function () use ($shipment, $linePayloads) {
            $shipment->load('lines');

            foreach ($shipment->lines as $line) {
                $data = Arr::get($linePayloads, $line->id, []);
                if (! $data) {
                    continue;
                }

                $line->update([
                    'picked_qty' => $data['picked_qty'],
                    'warehouse_id' => $data['warehouse_id'] ?? null,
                    'bin_id' => $data['bin_id'] ?? null,
                ]);
            }

            $shipment->status = 'picking';
            $shipment->save();
        });
    }
}
