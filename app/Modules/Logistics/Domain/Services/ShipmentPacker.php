<?php

namespace App\Modules\Logistics\Domain\Services;

use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LogicException;

class ShipmentPacker
{
    public function pack(Shipment $shipment, array $linePayloads, ?int $packagesCount, ?float $grossWeight, ?float $netWeight): void
    {
        if (! in_array($shipment->status, ['picking', 'packed'], true)) {
            throw new LogicException('Shipment cannot be packed in its current status.');
        }

        DB::transaction(function () use ($shipment, $linePayloads, $packagesCount, $grossWeight, $netWeight) {
            $shipment->load('lines');

            foreach ($shipment->lines as $line) {
                $data = Arr::get($linePayloads, $line->id, []);
                if (! $data) {
                    continue;
                }

                $line->update([
                    'packed_qty' => $data['packed_qty'],
                ]);
            }

            $shipment->fill([
                'packages_count' => $packagesCount,
                'gross_weight' => $grossWeight,
                'net_weight' => $netWeight,
                'status' => 'packed',
            ])->save();
        });
    }
}
