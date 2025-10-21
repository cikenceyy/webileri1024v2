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
        // Bridge old flat anonymous components (resources/views/components/ui-*.blade.php)
        // to dot-notation usage <x-ui.*> expected by module views.
        foreach (glob(resource_path('views/components/ui-*.blade.php')) as $path) {
            $file = basename($path, '.blade.php'); // e.g., ui-card
            $name = substr($file, 3);              // e.g., card
            // <x-ui.card> -> view('components.ui-card')
            Blade::component('components.' . $file, 'ui.' . $name);
        }

        // Optional: allow the inverse (<x-ui-card>) to use nested views as well.
        foreach (glob(resource_path('views/components/ui/*.blade.php')) as $path) {
            $name = basename($path, '.blade.php'); // e.g., card
            // <x-ui-card> -> view('components.ui.card')
            Blade::component('components.ui.' . $name, 'ui-' . $name);
        }

    }
}
