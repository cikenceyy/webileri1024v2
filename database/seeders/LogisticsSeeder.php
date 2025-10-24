<?php

namespace Database\Seeders;

use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use App\Modules\Logistics\Domain\Models\GoodsReceiptLine;
use App\Modules\Logistics\Domain\Models\Shipment;
use App\Modules\Logistics\Domain\Models\ShipmentLine;
use App\Modules\Logistics\Domain\Models\VarianceReason;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\SalesOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class LogisticsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedVarianceReasons();
        $this->seedShipments();
        $this->seedReceipts();
    }

    protected function seedVarianceReasons(): void
    {
        foreach ([1, 2] as $companyId) {
            foreach ([
                ['code' => 'over', 'name' => 'Tedarik Fazla'],
                ['code' => 'short', 'name' => 'Eksik Teslimat'],
                ['code' => 'damage', 'name' => 'Hasar'],
            ] as $reason) {
                VarianceReason::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'code' => $reason['code'],
                    ],
                    [
                        'name' => $reason['name'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    protected function seedShipments(): void
    {
        foreach ([1, 2] as $companyId) {
            $order = SalesOrder::query()
                ->where('company_id', $companyId)
                ->with('lines')
                ->first();

            $customer = $order ? $order->customer : Customer::query()->where('company_id', $companyId)->first();
            $warehouse = Warehouse::query()->where('company_id', $companyId)->first();

            if (! $customer || ! $warehouse) {
                continue;
            }

            $shipments = [
                ['code' => sprintf('SHP-%d-001', $companyId), 'status' => 'picking', 'packages' => 2],
                ['code' => sprintf('SHP-%d-002', $companyId), 'status' => 'packed', 'packages' => 3],
            ];

            foreach ($shipments as $index => $definition) {
                $shipment = Shipment::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'doc_no' => $definition['code'],
                    ],
                    [
                        'customer_id' => $customer->id,
                        'source_type' => $order ? SalesOrder::class : null,
                        'source_id' => $order?->id,
                        'status' => $definition['status'],
                        'warehouse_id' => $warehouse->id,
                        'packages_count' => $definition['packages'],
                        'gross_weight' => 150 + ($index * 10),
                        'net_weight' => 120 + ($index * 10),
                        'notes' => 'Seed shipment',
                    ]
                );

                $shipment->lines()->delete();

                $lines = $order?->lines ?? collect();

                if ($lines->isEmpty()) {
                    $product = Product::query()->where('company_id', $companyId)->first();
                    if ($product) {
                        $lines = collect([
                            (object) [
                                'product_id' => $product->id,
                                'qty' => 4,
                            ],
                        ]);
                    }
                }

                foreach ($lines as $lineIndex => $line) {
                    $qty = $line->qty ?? 4;
                    ShipmentLine::query()->create([
                        'company_id' => $companyId,
                        'shipment_id' => $shipment->id,
                        'product_id' => $line->product_id,
                        'qty' => $qty,
                        'uom' => 'adet',
                        'picked_qty' => $definition['status'] !== 'draft' ? $qty : 0,
                        'packed_qty' => $definition['status'] === 'packed' ? $qty : ($qty / 2),
                        'shipped_qty' => $definition['status'] === 'packed' ? $qty : 0,
                        'warehouse_id' => $warehouse->id,
                        'sort' => $lineIndex + 1,
                    ]);
                }
            }
        }
    }

    protected function seedReceipts(): void
    {
        foreach ([1, 2] as $companyId) {
            $warehouse = Warehouse::query()->where('company_id', $companyId)->orderBy('id', 'desc')->first();
            $product = Product::query()->where('company_id', $companyId)->first();

            if (! $warehouse || ! $product) {
                continue;
            }

            $timestamp = Carbon::now();
            $receipts = [
                ['code' => sprintf('GRN-%d-001', $companyId), 'status' => 'received', 'variance' => 0],
                ['code' => sprintf('GRN-%d-002', $companyId), 'status' => 'received', 'variance' => 1],
            ];

            foreach ($receipts as $index => $definition) {
                $receipt = GoodsReceipt::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'doc_no' => $definition['code'],
                    ],
                    [
                        'vendor_id' => null,
                        'status' => $definition['status'],
                        'warehouse_id' => $warehouse->id,
                        'received_at' => $timestamp->copy()->subHours(6 - ($index * 2)),
                        'notes' => 'Seed receipt',
                    ]
                );

                $receipt->lines()->delete();

                GoodsReceiptLine::query()->create([
                    'company_id' => $companyId,
                    'receipt_id' => $receipt->id,
                    'product_id' => $product->id,
                    'qty_expected' => 10,
                    'qty_received' => 10 - $definition['variance'],
                    'variance_reason' => $definition['variance'] !== 0 ? 'short' : null,
                    'warehouse_id' => $warehouse->id,
                ]);
            }
        }
    }
}
