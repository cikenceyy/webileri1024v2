<?php

namespace App\Modules\Marketing\Database\Seeders;

use App\Core\Support\Models\Company;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MarketingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $customers = $this->seedCustomers($company);
            $this->seedOrders($company, $customers);
        }
    }

    protected function seedCustomers(Company $company): Collection
    {
        $faker = fake();
        $customers = collect();

        for ($i = 1; $i <= 11; $i++) {
            $code = sprintf('C-%02d-%03d', $company->id, $i);
            $name = $faker->company();
            $email = sprintf('customer%02d@%s', $i, $company->domain ?? 'example.test');

            $customer = Customer::withTrashed()->firstOrNew([
                'company_id' => $company->id,
                'code' => $code,
            ]);

            $customer->fill([
                'name' => $name,
                'email' => $email,
                'phone' => $faker->phoneNumber(),
                'tax_no' => $faker->numerify('##########'),
                'address' => $faker->address(),
                'status' => $i % 5 === 0 ? 'inactive' : 'active',
            ]);

            $customer->save();

            if (method_exists($customer, 'trashed') && $customer->trashed()) {
                $customer->restore();
            }

            $customers->push($customer);
        }

        return $customers;
    }

    protected function seedOrders(Company $company, Collection $customers): void
    {
        if ($customers->isEmpty()) {
            return;
        }

        $faker = fake();
        $statuses = ['draft', 'draft', 'confirmed', 'confirmed', 'shipped', 'cancelled'];

        for ($i = 1; $i <= 11; $i++) {
            $orderNo = sprintf('ORD-%02d-%04d', $company->id, $i);
            $orderDate = now()->subDays(random_int(0, 30))->startOfDay();
            $dueDate = (clone $orderDate)->addDays(random_int(3, 21));
            $customer = $customers->random();

            $order = Order::withTrashed()->firstOrNew([
                'company_id' => $company->id,
                'order_no' => $orderNo,
            ]);

            $order->fill([
                'customer_id' => $customer->id,
                'order_date' => $orderDate,
                'due_date' => $dueDate,
                'currency' => Arr::random(['TRY', 'TRY', 'USD', 'EUR']),
                'status' => Arr::random($statuses),
                'total_amount' => $faker->randomFloat(2, 500, 25000),
                'notes' => $faker->sentence(8),
            ]);

            $order->save();

            if (method_exists($order, 'trashed') && $order->trashed()) {
                $order->restore();
            }
        }
    }
}
