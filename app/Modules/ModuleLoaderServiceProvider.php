<?php

namespace App\Modules;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ModuleLoaderServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>|null
     */
    protected ?array $modules = null;

    public function register(): void
    {
        foreach ($this->modules() as $path) {
            foreach ($this->discoverServiceProviders($path) as $provider) {
                if (class_exists($provider)) {
                    $this->app->register($provider);
                }
            }
        }
    }

    public function boot(): void
    {
        foreach ($this->modules() as $module => $path) {
            $this->registerRoutes($module, $path);
            $this->registerViews($module, $path);
            $this->registerTranslations($module, $path);
        }
    }

    /**
     * @return array<string, string>
     */
    protected function modules(): array
    {
        if ($this->modules !== null) {
            return $this->modules;
        }

        $modulesPath = app_path('Modules');

        if (! is_dir($modulesPath)) {
            return $this->modules = [];
        }

        $directories = File::directories($modulesPath);

        $modules = [];

        foreach ($directories as $directory) {
            $name = basename($directory);
            $modules[$name] = $directory;
        }

        ksort($modules);

        return $this->modules = $modules;
    }

    /**
     * @return array<int, class-string>
     */
    protected function discoverServiceProviders(string $path): array
    {
        $providers = glob($path . DIRECTORY_SEPARATOR . 'Providers' . DIRECTORY_SEPARATOR . '*ServiceProvider.php') ?: [];

        return array_values(array_filter(array_map(fn ($file) => $this->resolveClassFromPath($file), $providers)));
    }

    protected function resolveClassFromPath(string $file): ?string
    {
        $relative = Str::after($file, app_path() . DIRECTORY_SEPARATOR);

        if ($relative === '') {
            return null;
        }

        $class = str_replace(['/', '.php'], ['\\', ''], $relative);

        return 'App\\' . ltrim($class, '\\');
    }

    protected function registerRoutes(string $module, string $path): void
    {
        $routesPath = $path . DIRECTORY_SEPARATOR . 'Routes';

        if (! is_dir($routesPath)) {
            return;
        }

        $definitions = [
            'web' => ['file' => 'web.php', 'middleware' => ['web']],
            'admin' => ['file' => 'admin.php', 'middleware' => ['web']],
            'api' => ['file' => 'api.php', 'middleware' => ['api']],
        ];

        foreach ($definitions as $definition) {
            $file = $routesPath . DIRECTORY_SEPARATOR . $definition['file'];

            if (! file_exists($file)) {
                continue;
            }

            Route::middleware($definition['middleware'])
                ->group(static function () use ($file): void {
                    require $file;
                });
        }
    }

    protected function registerViews(string $module, string $path): void
    {
        $viewsPath = $path . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'views';

        if (is_dir($viewsPath)) {
            $namespace = Str::kebab($module);
            $this->loadViewsFrom($viewsPath, $namespace);
        }
    }

    protected function registerTranslations(string $module, string $path): void
    {
        $langPath = $path . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'lang';

        if (is_dir($langPath)) {
            $namespace = Str::kebab($module);
            $this->loadTranslationsFrom($langPath, $namespace);
        }
    }
}
