<?php

namespace App\Cms\Providers;

use App\Cms\Support\CmsRepository;
use App\Cms\Support\Seo;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class CmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/cms.php', 'cms');

        $this->app->singleton('cms.repository', function ($app) {
            return new CmsRepository($app);
        });

        $this->app->singleton('cms.seo', function ($app) {
            return new Seo($app->make('cms.repository'));
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/site.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'cms');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'cms');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');

        View::composer(['cms::site.partials.header', 'cms::site.partials.footer'], function ($view) {
            $view->with('cmsRepo', $this->app->make('cms.repository'));
        });

        // README NOTE: Provider registration lives in bootstrap/app.php providers stack.
    }
}
