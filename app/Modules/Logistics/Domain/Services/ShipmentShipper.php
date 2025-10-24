<?php

namespace App\Modules\Logistics\Domain\Services;

use App\Modules\Inventory\Domain\Models\StockLedgerEntry;
use App\Modules\Logistics\Domain\Models\Shipment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use LogicException;

class ShipmentShipper
{
    public function ship(Shipment $shipment): void
    {
        if ($shipment->status !== 'packed') {
            throw new LogicException('Shipment must be packed before shipping.');
        }

        DB::transaction(function () use ($shipment) {
            $shipment->load('lines');
            $timestamp = CarbonImmutable::now();

            foreach ($shipment->lines as $line) {
                $qty = (float) $line->packed_qty;
                if ($qty <= 0) {
                    continue;
                }

                $warehouseId = $line->warehouse_id ?: $shipment->warehouse_id;
                if (! $warehouseId) {
                    throw new LogicException('Shipment line için depo seçilmelidir.');
                }

                StockLedgerEntry::create([
                    'company_id' => $shipment->company_id,
                    'product_id' => $line->product_id,
                    'warehouse_id' => $warehouseId,
                    'bin_id' => $line->bin_id,
                    'qty_in' => 0,
                    'qty_out' => $qty,
                    'reason' => 'shipment',
                    'ref_type' => 'shipment',
                    'ref_id' => $shipment->id,
                    'doc_no' => $shipment->doc_no,
                    'dated_at' => $timestamp,
                ]);

                $line->update(['shipped_qty' => $qty]);
            }

            $shipment->forceFill([
                'status' => 'shipped',
                'shipped_at' => $timestamp,
            ])->save();
        });
    }
}
