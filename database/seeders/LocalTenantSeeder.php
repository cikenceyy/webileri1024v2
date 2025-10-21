<?php

namespace Database\Seeders;

use App\Core\Support\Models\Company;
use App\Core\Support\Models\CompanyDomain;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocalTenantSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Kobiname 1',
                'domain' => 'kobiname1.test',
                'theme_color' => '#4f46e5',
            ],
            [
                'name' => 'Kobiname 2',
                'domain' => 'kobiname2.test',
                'theme_color' => '#0891b2',
            ],
            [
                'name' => 'Kobiname 3',
                'domain' => 'kobiname3.test',
                'theme_color' => '#f97316',
            ],
        ];

        $guard = config('auth.defaults.guard', 'web');
        $permissionRegistrar = class_exists(\Spatie\Permission\PermissionRegistrar::class)
            ? app(\Spatie\Permission\PermissionRegistrar::class)
            : null;

        foreach ($companies as $data) {
            $company = Company::query()->updateOrCreate(
                ['domain' => $data['domain']],
                [
                    'name' => $data['name'],
                    'theme_color' => $data['theme_color'],
                ]
            );

            CompanyDomain::query()->updateOrCreate(
                ['domain' => 'www.' . $data['domain']],
                [
                    'company_id' => $company->id,
                    'is_primary' => false,
                ]
            );

            $ownerEmail = 'owner@' . $data['domain'];

            $user = User::query()->updateOrCreate(
                [
                    'email' => $ownerEmail,
                    'company_id' => $company->id,
                ],
                [
                    'name' => $data['name'] . ' Owner',
                    'password' => Hash::make('owner123456'),
                    'email_verified_at' => now(),
                ]
            );

            if ($permissionRegistrar && method_exists($user, 'assignRole')) {
                $permissionRegistrar->setPermissionsTeamId($company->id);

                if (class_exists(\Spatie\Permission\Models\Role::class)) {
                    \Spatie\Permission\Models\Role::findOrCreate('owner', $guard);
                }

                if (! $user->hasRole('owner')) {
                    $user->assignRole('owner');
                }
            }
        }

        if ($permissionRegistrar) {
            $permissionRegistrar->setPermissionsTeamId(null);
        }
    }
}
