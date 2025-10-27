<?php

use App\Cms\Providers\CmsServiceProvider;
use App\Consoles\Providers\ConsoleServiceProvider;
use App\Core\Providers\AccessServiceProvider;
use App\Core\Providers\CoreServiceProvider;
use App\Core\Providers\OrchestrationServiceProvider;
use App\Core\Providers\TenancyServiceProvider;
use App\Modules\ModuleLoaderServiceProvider;
use App\Core\Http\Middleware\EnsureIdempotency;
use App\Http\Middleware\HttpCacheHeaders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: __DIR__.'/../routes/health.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'http.cache' => HttpCacheHeaders::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'idempotency' => EnsureIdempotency::class,
        ]);
    })
    ->withProviders([
        TenancyServiceProvider::class,
        AccessServiceProvider::class,
        OrchestrationServiceProvider::class,
        ModuleLoaderServiceProvider::class,
        CoreServiceProvider::class,
        ConsoleServiceProvider::class,
        CmsServiceProvider::class,
        App\Providers\AppServiceProvider::class,
        App\Providers\ViewServiceProvider::class,
        App\Providers\EventServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $errorId = Str::upper(Str::ulid());

            Log::warning('Request resulted in 404.', [
                'error_id' => $errorId,
                'path' => $request->path(),
                'url' => $request->fullUrl(),
            ]);

            $view = $request->is('admin', 'admin/*') ? 'admin.errors.404' : 'errors.404';

            return response()->view($view, [
                'errorId' => $errorId,
                'requestPath' => $request->path(),
            ], 404);
        });
    })->create();