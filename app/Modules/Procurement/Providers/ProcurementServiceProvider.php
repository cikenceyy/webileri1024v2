<?php

namespace App\Modules\Procurement\Providers;

use App\Modules\Procurement\Domain\Models\PurchaseOrder;
use App\Modules\Procurement\Policies\PurchaseOrderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ProcurementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/permissions.php', 'procurement-permissions');
    }

    public function boot(): void
    {
        $basePath = __DIR__ . '/..';

        $this->loadRoutesFrom($basePath . '/Routes/admin.php');
        $this->loadViewsFrom($basePath . '/Resources/views', 'procurement');
        $this->loadMigrationsFrom($basePath . '/Database/migrations');

        Gate::policy(PurchaseOrder::class, PurchaseOrderPolicy::class);
    }
}
