<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        /** @var PermissionRegistrar $registrar */
        $registrar = app(PermissionRegistrar::class);
        $guard = config('auth.defaults.guard', 'web');

        $users = [
            [
                'name' => 'Platform Super Admin',
                'email' => 'super@platform.local',
                'company_id' => 1,
                'role' => 'super_admin',
            ],
            [
                'name' => 'Ayşe Yönetici',
                'email' => 'owner@acme.localhost',
                'company_id' => 1,
                'role' => 'owner',
            ],
            [
                'name' => 'Mehmet Muhasebe',
                'email' => 'accountant@acme.localhost',
                'company_id' => 1,
                'role' => 'accountant',
            ],
            [
                'name' => 'Selin Operasyon',
                'email' => 'operator@acme.localhost',
                'company_id' => 1,
                'role' => 'operator',
            ],
            [
                'name' => 'Emir Stajyer',
                'email' => 'intern@acme.localhost',
                'company_id' => 1,
                'role' => 'intern',
            ],
            [
                'name' => 'Betül Patron',
                'email' => 'owner@beta.localhost',
                'company_id' => 2,
                'role' => 'owner',
            ],
            [
                'name' => 'Kerem Muhasebe',
                'email' => 'accountant@beta.localhost',
                'company_id' => 2,
                'role' => 'accountant',
            ],
        ];

        foreach ($users as $data) {
            $user = User::query()->updateOrCreate(
                Arr::only($data, ['email']),
                [
                    'name' => $data['name'],
                    'company_id' => $data['company_id'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            Role::findOrCreate($data['role'], $guard);

            $registrar->setPermissionsTeamId($data['company_id']);
            $user->syncRoles([$data['role']]);
        }

        $registrar->setPermissionsTeamId(null);
    }
}
