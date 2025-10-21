<?php

namespace App\Modules\Logistics\Providers;

use App\Modules\Logistics\Domain\Models\Shipment;
use App\Modules\Logistics\Domain\Observers\ShipmentObserver;
use App\Modules\Logistics\Policies\ShipmentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class LogisticsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'logistics');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');

        Gate::policy(Shipment::class, ShipmentPolicy::class);
        Shipment::observe(ShipmentObserver::class);
    }
}
