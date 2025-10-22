<?php

use App\Core\Support\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (! class_exists(Permission::class) || ! class_exists(Role::class) || ! class_exists(PermissionRegistrar::class)) {
            return;
        }

        $registrar = app(PermissionRegistrar::class);
        $guard = config('permission.default_guard', config('auth.defaults.guard', 'web'));

        $companyIds = (class_exists(Company::class) && Schema::hasTable('companies'))
            ? Company::query()->pluck('id')
            : new Collection();

        if ($companyIds->isEmpty()) {
            $this->synchroniseForCompany(null, $registrar, $guard);
        } else {
            foreach ($companyIds as $companyId) {
                $this->synchroniseForCompany((int) $companyId, $registrar, $guard);
            }
        }

        $registrar->setPermissionsTeamId(null);
        $registrar->forgetCachedPermissions();
    }

    public function down(): void
    {
        if (! class_exists(Permission::class) || ! class_exists(PermissionRegistrar::class)) {
            return;
        }

        $registrar = app(PermissionRegistrar::class);
        $guard = config('permission.default_guard', config('auth.defaults.guard', 'web'));

        $permission = Permission::query()
            ->where('name', 'cms.manage')
            ->where('guard_name', $guard)
            ->first();

        if ($permission) {
            $permission->delete();
        }

        $registrar->setPermissionsTeamId(null);
        $registrar->forgetCachedPermissions();
    }

    protected function synchroniseForCompany(?int $companyId, PermissionRegistrar $registrar, string $guard): void
    {
        $registrar->setPermissionsTeamId($companyId);

        $permission = Permission::findOrCreate('cms.manage', $guard);

        foreach (['biz', 'patron', 'muhasebeci'] as $roleName) {
            $role = Role::findOrCreate($roleName, $guard);

            if (! $role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }
    }
};
