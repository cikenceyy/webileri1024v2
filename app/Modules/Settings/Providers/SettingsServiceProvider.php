<?php

namespace App\Modules\Settings\Providers;

use App\Core\Cache\Listeners\FlushSettingsCache;
use App\Core\Contracts\SettingsReader;
use App\Core\Support\Audit\SettingsAuditLogger;
use App\Modules\Settings\Domain\Events\SettingsUpdated;
use App\Modules\Settings\Domain\Models\Setting;
use App\Modules\Settings\Domain\SettingsService;
use App\Modules\Settings\Policies\SettingsPolicy;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeModuleConfig();

        $this->app->singleton(SettingsService::class, function ($app) {
            return new SettingsService(
                cache: $app->make(CacheRepository::class),
                database: $app->make('db'),
            );
        });

        $this->app->alias(SettingsService::class, SettingsReader::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'settings');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');

        Gate::policy(Setting::class, SettingsPolicy::class);

        Event::listen(SettingsUpdated::class, SettingsAuditLogger::class);
        Event::listen(SettingsUpdated::class, FlushSettingsCache::class);
    }

    protected function mergeModuleConfig(): void
    {
        if (! is_dir(__DIR__ . '/../Config')) {
            return;
        }

        foreach (glob(__DIR__ . '/../Config/*.php') ?: [] as $configFile) {
            $this->mergeConfigFrom($configFile, 'settings.' . basename($configFile, '.php'));
        }
    }
}
