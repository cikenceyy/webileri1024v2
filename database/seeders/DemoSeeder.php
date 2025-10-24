<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TenantSeeder::class,
            UserRoleSeeder::class,
            InventorySeeder::class,
            MarketingSeeder::class,
            SettingsSeeder::class,
            HrSeeder::class,
            ProductionSeeder::class,
            LogisticsSeeder::class,
            FinanceSeeder::class,
        ]);
    }
}
