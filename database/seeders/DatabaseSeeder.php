<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(GrantOwnerAllPermissionsSeeder::class);

        if (app()->environment('local') || (bool) env('SEED_DEMO_DATA', true)) {
            $this->call(DemoSeeder::class);
        }
    }
}
