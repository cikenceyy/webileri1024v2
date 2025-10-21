<?php

namespace App\Core\Providers;

use App\Core\Tenancy\Console\Commands\TenancyAuditCommand;
use App\Core\Tenancy\Middleware\IdentifyTenant;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->aliasMiddleware('tenant', IdentifyTenant::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                TenancyAuditCommand::class,
            ]);
        }
    }
}
