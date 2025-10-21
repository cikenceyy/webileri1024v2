<?php

namespace App\Consoles\Providers;

use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (is_file(__DIR__ . '/../Routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        }
        $this->loadRoutesFrom(__DIR__ . '/../Routes/console.php');

        $viewsPath = __DIR__ . '/../Resources/views';

        $this->loadViewsFrom($viewsPath, 'console');
        $this->loadViewsFrom($viewsPath, 'consoles');
    }
}
