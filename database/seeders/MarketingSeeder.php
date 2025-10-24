<?php

namespace Database\Seeders;

use App\Modules\Inventory\Domain\Models\PriceList;
use App\Modules\Inventory\Domain\Models\PriceListItem;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\ReturnRequest;
use App\Modules\Marketing\Domain\Models\ReturnRequestLine;
use App\Modules\Marketing\Domain\Models\SalesOrder;
use App\Modules\Marketing\Domain\Models\SalesOrderLine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        $customers = $this->seedCustomers();
        $orders = $this->seedOrders($customers);
        $this->seedReturns($orders);
    }

    protected function seedCustomers(): array
    {
        $catalogue = [
            1 => [
                ['Acme Metal Sanayi', 'acme-metal@example.com'],
                ['Ege İnşaat', 'ege-insaat@example.com'],
                ['Delta Enerji', 'delta-enerji@example.com'],
                ['Nova Otomasyon', 'nova-otomasyon@example.com'],
            ],
            2 => [
                ['ModaCity AVM', 'modacity@example.com'],
                ['Trend Tekstil', 'trend-tekstil@example.com'],
                ['Perakende Plus', 'perakende-plus@example.com'],
                ['Liman Market', 'liman-market@example.com'],
            ],
        ];

        $result = [];

        foreach ($catalogue as $companyId => $entries) {
            $priceList = PriceList::query()
                ->where('company_id', $companyId)
                ->orderBy('id')
                ->first();

            foreach ($entries as $index => [$name, $email]) {
                $code = sprintf('C%02d-%d', $companyId, $index + 1);
                $customer = Customer::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'email' => $email,
                    ],
                    [
                        'code' => $code,
                        'name' => $name,
                        'phone' => '+90 212 000 0' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                        'billing_address' => [
                            'line1' => 'Mah. Cad. Sk. No:' . ($index + 10),
                            'city' => 'İstanbul',
                            'country' => 'TR',
                        ],
                        'shipping_address' => [
                            'line1' => 'Depo Cad. No:' . ($index + 5),
                            'city' => 'İstanbul',
                            'country' => 'TR',
                        ],
                        'status' => 'active',
                        'is_active' => true,
                        'payment_terms_days' => 30,
                        'default_price_list_id' => $priceList?->id,
                        'credit_limit' => 250000,
                    ]
                );

                $result[$companyId][] = $customer;
            }
        }

        return $result;
    }

    protected function seedOrders(array $customers): array
    {
        $created = [];
        $now = Carbon::now();

        foreach ($customers as $companyId => $companyCustomers) {
            $products = Product::query()
                ->where('company_id', $companyId)
                ->take(4)
                ->get();

            $priceItems = PriceListItem::query()
                ->where('company_id', $companyId)
                ->get()
                ->keyBy('product_id');

            $orderCount = $companyId === 1 ? 4 : 2;

            for ($i = 0; $i < $orderCount; $i++) {
                $customer = $companyCustomers[$i % count($companyCustomers)];
                $docNo = sprintf('SO-%d-%04d', $companyId, $i + 1);
                $order = SalesOrder::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'doc_no' => $docNo,
                    ],
                    [
                        'customer_id' => $customer->id,
                        'price_list_id' => $customer->default_price_list_id,
                        'status' => SalesOrder::STATUS_CONFIRMED,
                        'currency' => 'TRY',
                        'tax_inclusive' => false,
                        'payment_terms_days' => 30,
                        'due_date' => $now->copy()->addDays(30),
                        'ordered_at' => $now->copy()->subDays(3 + $i),
                        'confirmed_at' => $now->copy()->subDays(2 + $i),
                        'notes' => 'Seed order ' . ($i + 1),
                    ]
                );

                $order->lines()->delete();

                foreach ($products as $index => $product) {
                    if ($index > 1) {
                        break;
                    }

                    $qty = $index === 0 ? 5 : 3;
                    $unitPrice = $priceItems->get($product->id)?->price ?? $product->price;
                    $lineTotal = $qty * $unitPrice;

                    SalesOrderLine::query()->create([
                        'company_id' => $companyId,
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'uom' => 'adet',
                        'unit_price' => $unitPrice,
                        'discount_pct' => 0,
                        'tax_rate' => 20,
                        'line_total' => $lineTotal,
                    ]);
                }

                $created[$companyId][] = $order;
            }
        }

        return $created;
    }

    protected function seedReturns(array $orders): void
    {
        foreach ($orders as $companyId => $companyOrders) {
            if (empty($companyOrders)) {
                continue;
            }

            $order = $companyOrders[0];
            $line = $order->lines()->first();
            if (! $line) {
                continue;
            }

            $return = ReturnRequest::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'customer_id' => $order->customer_id,
                    'related_order_id' => $order->id,
                ],
                [
                    'status' => ReturnRequest::STATUS_APPROVED,
                    'reason' => 'Ürün değişim talebi',
                    'notes' => 'Seed verisi: müşteri renk değişimi istedi.',
                ]
            );

            $return->lines()->delete();

            ReturnRequestLine::query()->create([
                'company_id' => $companyId,
                'return_id' => $return->id,
                'product_id' => $line->product_id,
                'qty' => 1,
                'reason_code' => 'color_mismatch',
            ]);
        }
    }
}
