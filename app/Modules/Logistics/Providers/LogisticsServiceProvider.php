<?php

namespace App\Modules\Logistics\Providers;

use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use App\Modules\Logistics\Domain\Models\Shipment;
use App\Modules\Logistics\Policies\ReceiptPolicy;
use App\Modules\Logistics\Policies\ShipmentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class LogisticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $configPath = __DIR__ . '/../Config';

        if (is_dir($configPath)) {
            foreach (glob($configPath . '/*.php') ?: [] as $configFile) {
                $name = pathinfo($configFile, PATHINFO_FILENAME);
                $this->mergeConfigFrom($configFile, 'logistics.' . $name);
            }
        }
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'logistics');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');

        $this->registerPolicies();
        $this->registerViewNamespaces();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Shipment::class, ShipmentPolicy::class);
        Gate::policy(GoodsReceipt::class, ReceiptPolicy::class);
    }

    protected function registerViewNamespaces(): void
    {
        $viewsPath = __DIR__ . '/../Resources/views';
        if (is_dir($viewsPath)) {
            View::addNamespace('logistics', $viewsPath);
        }

        $langPath = __DIR__ . '/../Resources/lang';
        if (is_dir($langPath)) {
            Lang::addNamespace('logistics', $langPath);
        }
    }
}
