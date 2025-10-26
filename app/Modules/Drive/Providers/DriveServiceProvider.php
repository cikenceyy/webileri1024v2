<?php

namespace App\Modules\Drive\Providers;

use App\Modules\Drive\Console\Commands\DriveHealthCommand;
use App\Modules\Drive\Console\Commands\DriveRefreshMeta;
use App\Modules\Drive\Console\Commands\DriveRehydrateUrlsCommand;
use App\Modules\Drive\Console\Commands\DriveScanOrphansCommand;
use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Drive\Policies\MediaPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class DriveServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/drive.php', 'drive');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DriveRefreshMeta::class,
                DriveHealthCommand::class,
                DriveRehydrateUrlsCommand::class,
                DriveScanOrphansCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'drive');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');

        $this->publishes([
            __DIR__ . '/../Config/drive.php' => config_path('drive.php'),
        ], 'drive-config');

        Gate::policy(Media::class, MediaPolicy::class);
    }
}
