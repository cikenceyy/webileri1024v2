<?php

namespace App\Core\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;

class AccessServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [];

    public function register(): void
    {
        parent::register();
    }

    public function boot(): void
    {
        $this->registerPolicies();

        if (! class_exists(PermissionRegistrar::class)) {
            return;
        }

        /** @var PermissionRegistrar $registrar */
        $registrar = app(PermissionRegistrar::class);

        $tenant = tenant();
        if (is_object($tenant) && isset($tenant->id)) {
            $registrar->setPermissionsTeamId((int) $tenant->id);
        }

        $resolved = $this->resolvePermissionCatalogue();

        Config::set('permissions.catalogue', $resolved);

        if (app()->runningInConsole()) {
            $registrar->forgetCachedPermissions();
        }

        Gate::before(static function ($user, $ability) {
            return method_exists($user, 'hasRole') && $user->hasRole('biz') ? true : null;
        });
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function resolvePermissionCatalogue(): array
    {
        $maps = [Config::get('permissions.map', [])];

        foreach ($this->discoverModulePermissionFiles() as $path) {
            $map = require $path;

            if (is_array($map)) {
                $maps[] = $map;
            }
        }

        return $this->mergePermissionMaps($maps);
    }

    /**
     * @return array<int, string>
     */
    protected function discoverModulePermissionFiles(): array
    {
        return glob(app_path('Modules/*/Config/permissions.php')) ?: [];
    }

    /**
     * @param array<int, mixed> $maps
     * @return array<string, array<int, string>>
     */
    protected function mergePermissionMaps(array $maps): array
    {
        $merged = [];

        foreach ($maps as $map) {
            if (! is_array($map)) {
                continue;
            }

            foreach ($map as $key => $permissions) {
                $key = (string) $key;
                $items = array_filter(
                    array_map(
                        static fn ($permission) => is_string($permission) ? trim($permission) : null,
                        Arr::flatten([Arr::wrap($permissions)])
                    )
                );

                if ($items === []) {
                    continue;
                }

                $merged[$key] ??= [];

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
}
