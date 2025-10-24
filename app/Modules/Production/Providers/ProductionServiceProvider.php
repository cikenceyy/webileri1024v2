<?php

namespace App\Modules\Production\Providers;

use App\Modules\Production\Domain\Models\Bom;
use App\Modules\Production\Domain\Models\WorkOrder;
use App\Modules\Production\Policies\BomPolicy;
use App\Modules\Production\Policies\WorkOrderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ProductionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/permissions.php', 'production-permissions');
    }

    public function boot(): void
    {
        $basePath = __DIR__ . '/..';

        $this->loadRoutesFrom($basePath . '/Routes/admin.php');
        $this->loadViewsFrom($basePath . '/Resources/views', 'production');
        $this->loadMigrationsFrom($basePath . '/Database/migrations');

        Gate::policy(WorkOrder::class, WorkOrderPolicy::class);
        Gate::policy(Bom::class, BomPolicy::class);
    }
}
