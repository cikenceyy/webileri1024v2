<?php

namespace App\Modules\Marketing\Providers;

use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Inventory\Domain\Models\PriceList;
use App\Modules\Marketing\Policies\CustomerPolicy;
use App\Modules\Marketing\Policies\OrderPolicy;
use App\Modules\Marketing\Policies\PriceListPolicy;
use App\Modules\Marketing\Policies\ReturnPolicy;
use App\Modules\Marketing\Domain\Models\SalesOrder;
use App\Modules\Marketing\Domain\Models\ReturnRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MarketingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $configPath = __DIR__ . '/../Config';

        if (is_dir($configPath)) {
            foreach (glob($configPath . '/*.php') ?: [] as $configFile) {
                $name = pathinfo($configFile, PATHINFO_FILENAME);
                $this->mergeConfigFrom($configFile, 'marketing.' . $name);
            }
        }
    }

    public function boot(): void
    {

        // Load module assets
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'marketing');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');

        $this->registerPolicies();
        $this->registerObservers();
        $this->registerCompatAliases();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(SalesOrder::class, OrderPolicy::class);
        Gate::policy(PriceList::class, PriceListPolicy::class);
        Gate::policy(ReturnRequest::class, ReturnPolicy::class);
    }

    protected function registerObservers(): void
    {
    }

    protected function registerCompatAliases(): void
    {
        $viewsPath = __DIR__ . '/../Resources/views';
        if (is_dir($viewsPath)) {
            View::addNamespace('marketing', $viewsPath);
            View::addNamespace('crmsales', $viewsPath);
            View::addNamespace('crm', $viewsPath);

            View::composer('marketing::*', static function ($view): void {
                $data = $view->getData();
                if (! Arr::has($data, 'module')) {
                    $view->with('module', 'Marketing');
                }
            });
        }

        $langPath = __DIR__ . '/../Resources/lang';
        if (is_dir($langPath)) {
            Lang::addNamespace('marketing', $langPath);
            Lang::addNamespace('crmsales', $langPath);
            Lang::addNamespace('crm', $langPath);
        }

        $moduleConfig = config('marketing.module', []);
        if ($moduleConfig !== []) {
            config(['crmsales.crm' => $moduleConfig]);
            config(['marketing.crm' => $moduleConfig]);
        }
    }
}
