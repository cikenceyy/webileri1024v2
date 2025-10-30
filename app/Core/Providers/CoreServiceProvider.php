<?php

namespace App\Core\Providers;

use App\Core\Cache\CacheEventLogger;
use App\Core\Cache\InvalidationService;
use App\Core\Cache\TenantCacheManager;
use App\Core\Console\Commands\AssignRole;
use App\Core\Console\Commands\ProjectCacheFlushCommand;
use App\Core\Console\Commands\ProjectCacheWarmCommand;
use App\Core\Console\Commands\TablekitScan;
use App\Core\Exports\Console\ExportsPurgeCommand;
use App\Core\TableKit\Console\TablekitRollupCommand;
use App\Core\TableKit\Services\MetricRecorder;
use App\Core\TableKit\Services\MetricRollupService;
use App\Core\TableKit\TableExporterRegistry;
use App\Core\Settings\Console\SettingsGetCommand;
use App\Core\Settings\Console\SettingsSetCommand;
use App\Core\Settings\SettingsRepository;
use App\Core\Support\Console\Commands\AppDoctorCommand;
use App\Core\Support\Console\Commands\CloudPostdeployCommand;
use App\Core\Support\Console\Commands\CloudPredeployCommand;
use App\Core\Support\Console\Commands\SequenceAuditCommand;
use App\Core\Support\Console\Commands\SequenceSeedCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CacheEventLogger::class);
        $this->app->singleton(InvalidationService::class);
        $this->app->singleton(TenantCacheManager::class);
        $this->app->singleton(SettingsRepository::class);
        $this->app->singleton(MetricRecorder::class);
        $this->app->singleton(MetricRollupService::class);
        $this->app->singleton(TableExporterRegistry::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                AppDoctorCommand::class,
                AssignRole::class,
                ProjectCacheFlushCommand::class,
                ProjectCacheWarmCommand::class,
                SequenceSeedCommand::class,
                SequenceAuditCommand::class,
                CloudPredeployCommand::class,
                CloudPostdeployCommand::class,
                TablekitScan::class,
                SettingsGetCommand::class,
                SettingsSetCommand::class,
                TablekitRollupCommand::class,
                ExportsPurgeCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        $this->loadAdminRoutes();
    }

    protected function loadAdminRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware('web')
            ->group(base_path('routes/admin.php'));
    }
}
