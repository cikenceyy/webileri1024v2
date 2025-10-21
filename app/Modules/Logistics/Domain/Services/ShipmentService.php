<?php

namespace App\Modules\Logistics\Domain\Services;

use App\Modules\Inventory\Domain\Services\StockService;
use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShipmentService
{
    public function startPicking(Shipment $shipment): Shipment
    {
        return $this->transition($shipment, 'picking', function (Shipment $model): void {
            $model->picking_started_at = now();
        }, ['draft']);
    }

    public function pack(Shipment $shipment): Shipment
    {
        return $this->transition($shipment, 'packed', function (Shipment $model): void {
            $model->packed_at = now();
        }, ['picking']);
    }

    public function ship(Shipment $shipment): Shipment
    {
        return DB::transaction(function () use ($shipment) {
            if (! in_array($shipment->status, ['picking', 'packed'], true)) {
                throw ValidationException::withMessages([
                    'status' => __('Sevkiyat sevk edilmeye hazır değil.'),
                ]);
            }

            $this->issueInventory($shipment);

            $shipment->status = 'shipped';
            $shipment->shipped_at = now();
            $shipment->save();

            return $shipment->fresh(['lines', 'packages', 'trackingEvents']);
        });
    }

    public function deliver(Shipment $shipment): Shipment
    {
        return $this->transition($shipment, 'delivered', function (Shipment $model): void {
            $model->delivered_at = now();
        }, ['shipped']);
    }

    public function markReturned(Shipment $shipment): Shipment
    {
        return $this->transition($shipment, 'returned', function (Shipment $model): void {
            $model->returned_at = now();
        }, ['shipped', 'delivered']);
    }

    protected function transition(Shipment $shipment, string $status, callable $callback, array $allowedCurrent): Shipment
    {
        if (! in_array($shipment->status, $allowedCurrent, true)) {
            throw ValidationException::withMessages([
                'status' => __('Bu işlem mevcut durum için izinli değil.'),
            ]);
        }

        return DB::transaction(function () use ($shipment, $status, $callback) {
            $callback($shipment);
            $shipment->status = $status;
            $shipment->save();

            return $shipment->fresh();
        });
    }

    protected function issueInventory(Shipment $shipment): void
    {
        if (! class_exists(StockService::class)) {
            return;
        }

        $shipment->loadMissing('warehouse', 'lines.product', 'lines.variant');

        $warehouse = $shipment->warehouse;
        if (! $warehouse) {
            return;
        }

        /** @var StockService $service */
        $service = app(StockService::class);

        foreach ($shipment->lines as $line) {
            $product = $line->product;
            if (! $product) {
                continue;
            }

            $service->issue(
                $warehouse,
                $product,
                $line->variant,
                (float) $line->quantity,
                [
                    'reason' => 'shipment',
                    'ref_type' => Shipment::class,
                    'ref_id' => $shipment->id,
                ]
            );
        }
    }
}
