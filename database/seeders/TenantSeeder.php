<?php

namespace Database\Seeders;

use App\Core\Support\Models\Company;
use App\Core\Support\Models\CompanyDomain;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $companies = [
            [
                'id' => 1,
                'name' => 'ACME Manufacturing',
                'domain' => 'acme.localhost',
                'theme_color' => '#2563eb',
                'extra_domains' => ['app.acme.localhost'],
            ],
            [
                'id' => 2,
                'name' => 'BETA Retail',
                'domain' => 'beta.localhost',
                'theme_color' => '#16a34a',
                'extra_domains' => ['app.beta.localhost'],
            ],
        ];

        foreach ($companies as $data) {
            DB::table('companies')->updateOrInsert(
                ['id' => $data['id']],
                [
                    'name' => $data['name'],
                    'domain' => $data['domain'],
                    'theme_color' => $data['theme_color'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            CompanyDomain::query()->updateOrCreate(
                ['domain' => $data['domain']],
                [
                    'company_id' => $data['id'],
                    'is_primary' => true,
                ]
            );

            foreach ($data['extra_domains'] as $domain) {
                CompanyDomain::query()->updateOrCreate(
                    ['domain' => $domain],
                    [
                        'company_id' => $data['id'],
                        'is_primary' => false,
                    ]
                );
            }
        }

        // refresh model cache so subsequent seeders can rely on Eloquent relations
        Company::query()->findOrFail(1);
        Company::query()->findOrFail(2);
    }
}
