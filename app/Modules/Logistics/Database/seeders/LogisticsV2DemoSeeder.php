<?php

namespace App\Modules\Logistics\Database\Seeders;

use App\Core\Support\Models\Company;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Logistics\Domain\Models\Carrier;
use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LogisticsV2DemoSeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->each(function (Company $company): void {
            $this->seedForCompany($company);
        });
    }

    protected function seedForCompany(Company $company): void
    {
        $carriers = collect([
            ['code' => 'YURTICI', 'name' => 'Yurtiçi Kargo'],
            ['code' => 'ARAS', 'name' => 'Aras Kargo'],
            ['code' => 'DHL', 'name' => 'DHL Express'],
        ])->map(function (array $carrier) use ($company) {
            return Carrier::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'code' => $carrier['code'],
                ],
                [
                    'name' => $carrier['name'],
                    'tracking_url' => 'https://tracking.example/' . Str::lower($carrier['code']),
                    'active' => true,
                ]
            );
        });

        $customers = class_exists(Customer::class)
            ? Customer::query()->where('company_id', $company->id)->get()
            : collect();
        $products = class_exists(Product::class)
            ? Product::query()->where('company_id', $company->id)->with('variants')->get()
            : collect();
        $warehouse = class_exists(Warehouse::class)
            ? Warehouse::query()->where('company_id', $company->id)->first()
            : null;

        $statuses = ['draft', 'picking', 'packed', 'shipped', 'delivered', 'returned'];
        $baseDate = now()->startOfDay();

        for ($i = 1; $i <= 10; $i++) {
            $status = $statuses[$i % count($statuses)];
            $shipDate = $baseDate->copy()->subDays(15 - $i);
            $carrier = $carriers[$i % $carriers->count()] ?? $carriers->first();
            $customer = $customers[$i % max($customers->count(), 1)] ?? $customers->first();
            $order = ($customer && method_exists($customer, 'orders'))
                ? $customer->orders()->latest('order_date')->first()
                : null;

            $shipment = Shipment::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'shipment_no' => 'SHP-' . $company->id . '-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                ],
                [
                    'ship_date' => $shipDate,
                    'status' => $status,
                    'customer_id' => $customer?->id,
                    'order_id' => $order?->id,
                    'warehouse_id' => $warehouse?->id,
                    'carrier_id' => $carrier?->id,
                    'carrier' => $carrier?->name,
                    'tracking_no' => 'TRK-' . $shipDate->format('Ymd') . '-' . $i,
                    'package_count' => 0,
                    'weight_kg' => rand(5, 50),
                    'volume_dm3' => rand(10, 80),
                    'shipping_cost' => rand(100, 400) / 1.0,
                    'notes' => 'Demo shipment #' . $i,
                    'picking_started_at' => in_array($status, ['picking','packed','shipped','delivered','returned'], true) ? $shipDate->copy()->subDays(1) : null,
                    'packed_at' => in_array($status, ['packed','shipped','delivered','returned'], true) ? $shipDate->copy()->subHours(12) : null,
                    'shipped_at' => in_array($status, ['shipped','delivered','returned'], true) ? $shipDate : null,
                    'delivered_at' => in_array($status, ['delivered'], true) ? $shipDate->copy()->addDays(2) : null,
                    'returned_at' => $status === 'returned' ? $shipDate->copy()->addDays(3) : null,
                ]
            );

            $shipment->lines()->delete();
            $lineProducts = $products->shuffle()->take(2);
            if ($lineProducts->isEmpty()) {
                $shipment->lines()->create([
                    'company_id' => $company->id,
                    'description' => 'Demo item',
                    'quantity' => 1,
                    'unit' => 'pcs',
                    'weight_kg' => rand(1, 5),
                    'sort_order' => 0,
                ]);
            } else {
                foreach ($lineProducts as $idx => $product) {
                    $variant = $product->variants->random() ?: null;
                    $shipment->lines()->create([
                        'company_id' => $company->id,
                        'product_id' => $product->id,
                        'variant_id' => $variant?->id,
                        'description' => $product->name,
                        'quantity' => rand(1, 4),
                        'unit' => $product->base_unit_id && method_exists($product, 'baseUnit') ? $product->baseUnit?->code ?? 'pcs' : 'pcs',
                        'weight_kg' => rand(1, 8),
                        'notes' => $variant?->sku,
                        'sort_order' => $idx,
                    ]);
                }
            }

            $shipment->packages()->delete();
            $packageCount = rand(1, 3);
            for ($p = 0; $p < $packageCount; $p++) {
                $shipment->packages()->create([
                    'company_id' => $company->id,
                    'reference' => 'PKG-' . $i . '-' . ($p + 1),
                    'weight_kg' => rand(5, 20),
                    'length_cm' => rand(20, 60),
                    'width_cm' => rand(15, 40),
                    'height_cm' => rand(10, 35),
                    'notes' => $p === 0 ? 'Contains fragile items' : null,
                ]);
            }

            $shipment->update(['package_count' => $shipment->packages()->count() ?: null]);

            $shipment->trackingEvents()->delete();
            if ($status !== 'draft') {
                $events = [
                    ['status' => 'Picking started', 'offset' => -1],
                    ['status' => 'Packed', 'offset' => -1, 'hours' => -6],
                    ['status' => 'Shipped', 'offset' => 0],
                ];
                if ($status === 'delivered') {
                    $events[] = ['status' => 'Delivered', 'offset' => 2];
                }
                if ($status === 'returned') {
                    $events[] = ['status' => 'Return initiated', 'offset' => 2];
                    $events[] = ['status' => 'Returned to warehouse', 'offset' => 3];
                }

                foreach ($events as $event) {
                    $shipment->trackingEvents()->create([
                        'company_id' => $company->id,
                        'status' => $event['status'],
                        'recorded_at' => $shipDate->copy()->addDays($event['offset'])->addHours($event['hours'] ?? 0),
                        'description' => 'Demo tracking event',
                        'location' => 'Istanbul',
                    ]);
                }
            }
        }
    }
}
