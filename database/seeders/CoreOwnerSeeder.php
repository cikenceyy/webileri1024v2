<?php

namespace Database\Seeders;

use App\Core\Support\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CoreOwnerSeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->each(function (Company $company): void {
            $email = 'owner@' . Str::lower($company->domain);

            $user = User::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'email' => $email,
                ],
                [
                    'name' => trim($company->name . ' Owner'),
                    'password' => Hash::make('owner123456'),
                ]
            );

            if (method_exists($user, 'assignRole')) {
                $user->assignRole('owner');
            }
        });
    }
}
