<?php

use App\Consoles\ConsoleServiceProvider;
use App\Core\Providers\AccessServiceProvider;
use App\Core\Providers\CoreServiceProvider;
use App\Core\Providers\OrchestrationServiceProvider;
use App\Core\Providers\TenancyServiceProvider;
use App\Modules\ModuleLoaderServiceProvider;
use App\Http\Middleware\HttpCacheHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: __DIR__.'/../routes/health.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'http.cache' => HttpCacheHeaders::class,
        ]);
    })
    ->withProviders([
        TenancyServiceProvider::class,
        AccessServiceProvider::class,
        OrchestrationServiceProvider::class,
        ModuleLoaderServiceProvider::class,
        CoreServiceProvider::class,
        ConsoleServiceProvider::class,
        App\Providers\AppServiceProvider::class,
        App\Providers\EventServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
