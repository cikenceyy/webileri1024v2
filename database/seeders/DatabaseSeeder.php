<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Önce şirketler (tenant)
        $this->call(TenantSeeder::class);

        // 2) Roller & izinler
        $this->call(RolesAndPermissionsSeeder::class);

        // 3) Owner’a yetkiler (artık şirketler var)
        $this->call(GrantOwnerAllPermissionsSeeder::class);

        // 4) Demo verisi (kalanlar)
        if (app()->environment('local') || (bool) env('SEED_DEMO_DATA', true)) {
            $this->call([
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
}
