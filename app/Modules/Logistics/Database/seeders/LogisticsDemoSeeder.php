<?php

namespace App\Modules\Logistics\Database\Seeders;

use App\Core\Support\Models\Company;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LogisticsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::query()->get();

        if ($companies->isEmpty()) {
            return;
        }

        $statusPool = ['draft', 'preparing', 'in_transit', 'delivered', 'cancelled'];
        $carrierPool = ['Yurtiçi Kargo', 'Aras Kargo', 'MNG Kargo', 'DHL Express', 'UPS'];

        foreach ($companies as $company) {
            $customers = Customer::query()
                ->where('company_id', $company->id)
                ->get();

            $orders = Order::query()
                ->where('company_id', $company->id)
                ->with('customer')
                ->get();

            for ($index = 1; $index <= 11; $index++) {
                $shipmentNo = sprintf('DEMO-SHP-%d-%03d', $company->id, $index);
                $shipDate = now()->subDays(rand(0, 30))->startOfDay();
                $status = Arr::random($statusPool);
                $carrier = Arr::random($carrierPool);

                $order = $orders->isNotEmpty() ? $orders->random() : null;
                $customer = $order?->customer;

                if (! $customer && $customers->isNotEmpty()) {
                    $customer = $customers->random();
                }

                Shipment::query()->updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'shipment_no' => $shipmentNo,
                    ],
                    [
                        'ship_date' => $shipDate,
                        'status' => $status,
                        'customer_id' => $customer?->id,
                        'order_id' => $order?->id,
                        'carrier' => $carrier,
                        'tracking_no' => 'TRK-' . Str::upper(Str::random(6)) . '-' . str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                        'package_count' => rand(1, 6),
                        'weight_kg' => round(rand(5, 60) + (rand(0, 900) / 1000), 3),
                        'volume_dm3' => round(rand(10, 150) + (rand(0, 900) / 1000), 3),
                        'notes' => 'Demo sevkiyat kaydı - ' . $carrier,
                    ]
                );
            }
        }
    }
}
