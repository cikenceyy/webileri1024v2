<?php

namespace App\Modules\Settings\Providers;

use App\Core\Support\Models\Company;
use App\Core\Support\Models\CompanyDomain;
use App\Modules\Settings\Policies\CompanyDomainPolicy;
use App\Modules\Settings\Policies\CompanyPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (is_dir(__DIR__ . '/../Config')) {
            foreach (glob(__DIR__ . '/../Config/*.php') ?: [] as $configFile) {
                $this->mergeConfigFrom($configFile, 'settings.' . basename($configFile, '.php'));
            }
        }

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'settings');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');

        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(CompanyDomain::class, CompanyDomainPolicy::class);
    }
}
