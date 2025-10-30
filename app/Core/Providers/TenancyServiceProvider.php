<?php

namespace App\Core\Providers;

use App\Core\Tenancy\Console\Commands\TenancyAuditCommand;
use App\Core\Tenancy\Console\Commands\TenantDomainAddCommand;
use App\Core\Tenancy\Console\Commands\TenantDomainListCommand;
use App\Core\Tenancy\Console\Commands\TenantDomainRemoveCommand;
use App\Core\Tenancy\Console\Commands\TenantFlushDomainCacheCommand;
use App\Core\Tenancy\Console\Commands\TenantUserProvisionCommand;
use App\Core\Tenancy\DomainCacheManager;
use App\Core\Tenancy\Middleware\IdentifyTenant;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DomainCacheManager::class);
    }

    public function boot(Router $router): void
    {
        $router->aliasMiddleware('tenant', IdentifyTenant::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                TenancyAuditCommand::class,
                TenantDomainAddCommand::class,
                TenantDomainListCommand::class,
                TenantDomainRemoveCommand::class,
                TenantFlushDomainCacheCommand::class,
                TenantUserProvisionCommand::class,
            ]);
        }
    }
}
