<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $helpers = app_path('Support/helpers.php');

        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // UI components rely on Laravel's automatic anonymous component discovery.
        // No manual Blade aliases are needed for <x-ui-*> usage.
        Vite::useBuildDirectory('cms');

        RateLimiter::for('tablekit-list', function (Request $request): Limit {
            $companyId = function_exists('currentCompanyId') ? (int) currentCompanyId() : 0;
            $identifier = ($request->user()?->id ?? $request->ip()) . ':' . $companyId;

            return Limit::perMinute(30)->by($identifier);
        });

        RateLimiter::for('tablekit-export', function (Request $request): Limit {
            $companyId = function_exists('currentCompanyId') ? (int) currentCompanyId() : 0;
            $identifier = ($request->user()?->id ?? $request->ip()) . ':' . $companyId;

            return Limit::perMinute(5)->by($identifier);
        });
    }
}
