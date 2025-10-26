<?php

namespace App\Core\Providers;

use App\Core\Console\Commands\AssignRole;
use App\Core\Support\Console\Commands\AppDoctorCommand;
use App\Core\Support\Console\Commands\CloudPostdeployCommand;
use App\Core\Support\Console\Commands\CloudPredeployCommand;
use App\Core\Support\Console\Commands\FlushTenantDomainCacheCommand;
use App\Core\Support\Console\Commands\SequenceAuditCommand;
use App\Core\Support\Console\Commands\SequenceSeedCommand;
use App\Core\Console\Commands\TablekitScan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                AppDoctorCommand::class,
                AssignRole::class,
                FlushTenantDomainCacheCommand::class,
                SequenceSeedCommand::class,
                SequenceAuditCommand::class,
                CloudPredeployCommand::class,
                CloudPostdeployCommand::class,
                TablekitScan::class,
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
