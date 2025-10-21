<?php

namespace App\Modules\Marketing\Database\Seeders;

use App\Modules\Marketing\Domain\Models\Activity;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\CustomerAddress;
use App\Modules\Marketing\Domain\Models\CustomerContact;
use App\Modules\Marketing\Domain\Models\Attachment;
use App\Modules\Marketing\Domain\Models\Note;
use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Marketing\Domain\Models\OrderLine;
use App\Modules\Marketing\Domain\Models\Quote;
use App\Modules\Marketing\Domain\Models\QuoteLine;
use App\Modules\Drive\Domain\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketingAdvancedDemoSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Customer::query()->select('company_id')->distinct()->pluck('company_id');

        foreach ($companies as $companyId) {
            $customers = Customer::where('company_id', $companyId)->get();

            if ($customers->count() < 5) {
                for ($i = $customers->count(); $i < 5; $i++) {
                    $customers->push(Customer::create([
                        'company_id' => $companyId,
                        'code' => 'C-' . $companyId . '-' . Str::padLeft((string) $i, 3, '0'),
                        'name' => 'Demo Customer ' . ($i + 1),
                        'email' => 'customer' . $i . '@example.com',
                        'status' => 'active',
                    ]));
                }
            }

            foreach ($customers as $customer) {
                CustomerContact::firstOrCreate([
                    'company_id' => $companyId,
                    'customer_id' => $customer->id,
                    'email' => $customer->email,
                ], [
                    'name' => $customer->name . ' Contact',
                    'phone' => '+90' . random_int(100000000, 999999999),
                    'is_primary' => true,
                ]);

                CustomerAddress::firstOrCreate([
                    'company_id' => $companyId,
                    'customer_id' => $customer->id,
                    'type' => 'billing',
                ], [
                    'line1' => 'Demo Street 1',
                    'city' => 'Istanbul',
                    'country' => 'TR',
                    'is_primary' => true,
                ]);
            }

            for ($i = 0; $i < 15; $i++) {
                $customer = $customers->random();
                $quote = Quote::updateOrCreate([
                    'company_id' => $companyId,
                    'quote_no' => 'QUO-' . $companyId . '-' . Str::padLeft((string) $i, 4, '0'),
                ], [
                    'customer_id' => $customer->id,
                    'date' => now()->subDays($i),
                    'currency' => 'TRY',
                    'status' => 'draft',
                    'subtotal' => 0,
                    'discount_total' => 0,
                    'tax_total' => 0,
                    'grand_total' => 0,
                ]);

                $quote->lines()->delete();
                $subtotal = 0;
                $net = 0;
                for ($line = 0; $line < 3; $line++) {
                    $qty = random_int(1, 5);
                    $price = random_int(100, 500);
                    $lineTotal = $qty * $price;
                    $net += $lineTotal;
                    QuoteLine::create([
                        'company_id' => $companyId,
                        'quote_id' => $quote->id,
                        'description' => 'Quote line ' . ($line + 1),
                        'qty' => $qty,
                        'unit' => 'pcs',
                        'unit_price' => $price,
                        'discount_rate' => 0,
                        'tax_rate' => config('marketing.module.default_tax_rate'),
                        'line_total' => $lineTotal,
                        'sort_order' => $line,
                    ]);
                    $subtotal += $lineTotal;
                }
                $tax = $net * config('marketing.module.default_tax_rate') / 100;
                $quote->update([
                    'subtotal' => $subtotal,
                    'discount_total' => 0,
                    'tax_total' => $tax,
                    'grand_total' => $net + $tax,
                ]);
            }

            for ($i = 0; $i < 12; $i++) {
                $customer = $customers->random();
                $order = Order::updateOrCreate([
                    'company_id' => $companyId,
                    'order_no' => 'ORD-' . $companyId . '-' . Str::padLeft((string) $i, 4, '0'),
                ], [
                    'customer_id' => $customer->id,
                    'order_date' => now()->subDays($i),
                    'currency' => 'TRY',
                    'status' => 'confirmed',
                    'subtotal' => 0,
                    'discount_total' => 0,
                    'tax_total' => 0,
                    'total_amount' => 0,
                ]);

                $order->lines()->delete();
                $subtotal = 0;
                for ($line = 0; $line < 3; $line++) {
                    $qty = random_int(1, 5);
                    $price = random_int(120, 600);
                    $lineTotal = $qty * $price;
                    OrderLine::create([
                        'company_id' => $companyId,
                        'order_id' => $order->id,
                        'description' => 'Order line ' . ($line + 1),
                        'qty' => $qty,
                        'unit' => 'pcs',
                        'unit_price' => $price,
                        'discount_rate' => 0,
                        'tax_rate' => config('marketing.module.default_tax_rate'),
                        'line_total' => $lineTotal,
                        'sort_order' => $line,
                    ]);
                    $subtotal += $lineTotal;
                }
                $tax = $subtotal * config('marketing.module.default_tax_rate') / 100;
                $order->update([
                    'subtotal' => $subtotal,
                    'discount_total' => 0,
                    'tax_total' => $tax,
                    'total_amount' => $subtotal + $tax,
                ]);
            }

            for ($i = 0; $i < 25; $i++) {
                Activity::firstOrCreate([
                    'company_id' => $companyId,
                    'subject' => 'Follow up #' . $i,
                    'related_type' => Customer::class,
                    'related_id' => $customers->random()->id,
                ], [
                    'type' => 'call',
                    'due_at' => now()->addDays($i),
                ]);
            }

            for ($i = 0; $i < 30; $i++) {
                $customer = $customers->random();
                Note::firstOrCreate([
                    'company_id' => $companyId,
                    'related_type' => Customer::class,
                    'related_id' => $customer->id,
                    'body' => 'Seed note #' . $i . ' for ' . $customer->name,
                ], [
                    'created_by' => null,
                ]);
            }

            $mediaPool = Media::where('company_id', $companyId)->pluck('id');
            if ($mediaPool->isNotEmpty()) {
                for ($i = 0; $i < 20; $i++) {
                    $customer = $customers->random();
                    Attachment::firstOrCreate([
                        'company_id' => $companyId,
                        'related_type' => Customer::class,
                        'related_id' => $customer->id,
                        'media_id' => $mediaPool->random(),
                    ], [
                        'uploaded_by' => null,
                    ]);
                }
            }
        }
    }
}
