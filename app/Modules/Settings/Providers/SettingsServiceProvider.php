<?php

namespace App\Modules\Settings\Providers;

use App\Modules\Settings\Application\Services\SettingsService;
use App\Modules\Settings\Application\Services\SettingsServiceInterface;
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
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');

        $this->app->singleton(SettingsServiceInterface::class, SettingsService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
    }
}
