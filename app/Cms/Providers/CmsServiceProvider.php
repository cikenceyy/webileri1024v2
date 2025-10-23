<?php

namespace App\Cms\Providers;

use App\Cms\Models\ContactMessage;
use App\Cms\Support\AuditLogger;
use App\Cms\Support\CmsRepository;
use App\Cms\Support\PreviewStore;
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

        $this->app->singleton(PreviewStore::class, function ($app) {
            return new PreviewStore($app->make('cache.store'), $app['request']);
        });

        $this->app->singleton('cms.seo', function ($app) {
            return new Seo($app->make('cms.repository'), $app->make(PreviewStore::class));
        });

        $this->app->singleton(AuditLogger::class, function ($app) {
            return new AuditLogger($app->make('cms.repository'));
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

        View::composer('partials._sidebar', function ($view) {
            $count = 0;

            if (class_exists(ContactMessage::class)) {
                try {
                    $repository = $this->app->make('cms.repository');
                    $count = ContactMessage::query()
                        ->where('company_id', $repository->companyId())
                        ->where(function ($query) {
                            $query->whereNull('read_at')->orWhere('is_read', false);
                        })
                        ->count();
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }

            $view->with('cmsUnreadMessages', $count);
        });

        // README NOTE: Provider registration lives in bootstrap/app.php providers stack.
    }
}
