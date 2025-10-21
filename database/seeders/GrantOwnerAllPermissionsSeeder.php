<?php

namespace Database\Seeders;

use App\Core\Support\Models\Company;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class GrantOwnerAllPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        /** @var PermissionRegistrar $registrar */
        $registrar = app(PermissionRegistrar::class);

        Company::query()->each(function (Company $company) use ($registrar): void {
            // Set current team context
            $registrar->setPermissionsTeamId($company->id);

            // Ensure role exists
            $role = Role::findOrCreate('owner', 'web');

            // Grant all current permissions to 'owner' for this team
            $role->syncPermissions(Permission::all());
        });

        // Reset context
        $registrar->setPermissionsTeamId(null);
    }
}
