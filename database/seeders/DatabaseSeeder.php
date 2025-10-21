<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        if (app()->environment('local') || (bool) env('SEED_LOCAL_KOBIS', false)) {
            $this->call(LocalTenantSeeder::class);
        }

        if (class_exists(\App\Modules\Drive\Database\Seeders\DriveDemoSeeder::class)) {
            $this->call(\App\Modules\Drive\Database\Seeders\DriveDemoSeeder::class);
        }

        if (class_exists(\App\Modules\Inventory\Database\Seeders\InventoryBasicsDemoSeeder::class)) {
            $this->call(\App\Modules\Inventory\Database\Seeders\InventoryBasicsDemoSeeder::class);
        }

        if (class_exists(\App\Modules\Inventory\Database\Seeders\InventoryStockDemoSeeder::class)) {
            $this->call(\App\Modules\Inventory\Database\Seeders\InventoryStockDemoSeeder::class);
        }

        if (class_exists(\App\Modules\Marketing\Database\Seeders\MarketingAdvancedDemoSeeder::class)) {
            $this->call(\App\Modules\Marketing\Database\Seeders\MarketingAdvancedDemoSeeder::class);
        }

        if (class_exists(\App\Modules\Logistics\Database\Seeders\LogisticsDemoSeeder::class)) {
            $this->call(\App\Modules\Logistics\Database\Seeders\LogisticsDemoSeeder::class);
        }

        if (class_exists(\App\Modules\Finance\Database\Seeders\FinanceV2DemoSeeder::class)) {
            $this->call(\App\Modules\Finance\Database\Seeders\FinanceV2DemoSeeder::class);
        }
    }
}
