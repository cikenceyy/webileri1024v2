<?php

namespace App\Core\Providers;

use App\Core\Orchestrations\MakeToOrderOrchestration;
use App\Core\Orchestrations\OrderToCashOrchestration;
use App\Core\Orchestrations\ProcureToPayOrchestration;
use Illuminate\Support\ServiceProvider;

class OrchestrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OrderToCashOrchestration::class);
        $this->app->singleton(ProcureToPayOrchestration::class);
        $this->app->singleton(MakeToOrderOrchestration::class);
    }

    public function boot(): void
    {
        // Hook points for future queue listeners and workflow telemetry.
    }
}
