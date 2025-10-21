<?php

namespace App\Consoles;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $routesFile = app_path('Consoles/Routes/console.php');

        if (! file_exists($routesFile)) {
            return;
        }

        Route::middleware(['web', 'tenant', 'auth', 'verified'])
            ->group(static function () use ($routesFile): void {
                require $routesFile;
            });
    }
}
