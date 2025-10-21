<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
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
        // Allow legacy hyphen syntax (<x-ui-card>) to continue resolving to the
        // canonical dot-notation components that now live under
        // resources/views/components/ui/...
        foreach (glob(resource_path('views/components/ui/*.blade.php')) as $path) {
            $name = basename($path, '.blade.php');

            Blade::component('components.ui.' . $name, 'ui-' . $name);
        }

    }
}
