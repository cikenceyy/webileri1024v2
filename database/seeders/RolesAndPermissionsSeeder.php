<?php

namespace Database\Seeders;

use App\Core\Support\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        if (! class_exists(Role::class) || ! class_exists(Permission::class)) {
            return;
        }

        if (! $this->shouldSeed()) {
            return;
        }

        $companyId = $this->resolveCompanyId();

        /** @var PermissionRegistrar $registrar */
        $registrar = app(PermissionRegistrar::class);

        if ($companyId !== null) {
            $registrar->setPermissionsTeamId($companyId);
        }

        $catalogue = $this->permissionCatalogue();
        $allPermissions = $this->flattenPermissions($catalogue);

        foreach ($allPermissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $roleMatrix = $this->roleMatrix($allPermissions);

        foreach ($roleMatrix as $role => $permissionSet) {
            $roleModel = Role::findOrCreate($role, 'web');
            $roleModel->syncPermissions($permissionSet);
        }

        $registrar->forgetCachedPermissions();
    }

    protected function shouldSeed(): bool
    {
        $url = (string) config('app.url');

        return app()->environment('local') || Str::contains($url, 'localhost');
    }

    protected function resolveCompanyId(): ?int
    {
        $company = Company::query()->first();

        if (! $company) {
            $company = Company::factory()->create([
                'name' => 'Demo Company',
                'domain' => 'demo.localhost',
            ]);
        }

        return $company?->id;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function permissionCatalogue(): array
    {
        $maps = [Config::get('permissions.map', [])];

        foreach (glob(app_path('Modules/*/Config/permissions.php')) ?: [] as $path) {
            $map = require $path;

            if (is_array($map)) {
                $maps[] = $map;
            }
        }

        $merged = [];

        foreach ($maps as $map) {
            if (! is_array($map)) {
                continue;
            }

            foreach ($map as $key => $permissions) {
                $key = (string) $key;
                $merged[$key] ??= [];

                $items = array_filter(array_map(
                    static fn ($permission) => is_string($permission) ? trim($permission) : null,
                    Arr::flatten([Arr::wrap($permissions)])
                ));

                foreach ($items as $permission) {
                    if (! in_array($permission, $merged[$key], true)) {
                        $merged[$key][] = $permission;
                    }
                }
            }
        }

        ksort($merged);

        foreach ($merged as &$values) {
            sort($values);
        }

        return $merged;
    }

    /**
     * @param array<string, array<int, string>> $catalogue
     * @return array<int, string>
     */
    protected function flattenPermissions(array $catalogue): array
    {
        $permissions = [];

        foreach ($catalogue as $values) {
            foreach ($values as $permission) {
                if (! in_array($permission, $permissions, true)) {
                    $permissions[] = $permission;
                }
            }
        }

        sort($permissions);

        return $permissions;
    }

    /**
     * @param array<int, string> $permissions
     * @return array<string, array<int, string>>
     */
    protected function roleMatrix(array $permissions): array
    {
        $readOnly = array_values(array_filter($permissions, static function (string $permission): bool {
            return preg_match('/\.(view|index|show)$/', $permission) === 1;
        }));

        $finance = array_values(array_filter($permissions, static function (string $permission): bool {
            return str_starts_with($permission, 'finance.');
        }));

        $full = $permissions;

        return [
            'biz' => $full,
            'patron' => $full,
            'muhasebeci' => array_values(array_unique(array_merge($finance, $readOnly))),
            'stajyer' => $readOnly,
        ];
    }
}
