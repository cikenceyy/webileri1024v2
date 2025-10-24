<?php

namespace App\Modules\Finance\Providers;

use App\Modules\Finance\Domain\Models\CashbookEntry;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use App\Modules\Finance\Policies\CashbookPolicy;
use App\Modules\Finance\Policies\InvoicePolicy;
use App\Modules\Finance\Policies\ReceiptPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $configPath = __DIR__ . '/../Config';

        if (is_dir($configPath)) {
            foreach (glob($configPath . '/*.php') ?: [] as $configFile) {
                $name = pathinfo($configFile, PATHINFO_FILENAME);
                $this->mergeConfigFrom($configFile, 'finance.' . $name);
            }
        }
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'finance');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');

        $this->registerPolicies();
        $this->registerViewNamespaces();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Receipt::class, ReceiptPolicy::class);
        Gate::policy(CashbookEntry::class, CashbookPolicy::class);
    }

    protected function registerViewNamespaces(): void
    {
        $viewsPath = __DIR__ . '/../Resources/views';
        if (is_dir($viewsPath)) {
            View::addNamespace('finance', $viewsPath);
        }

        $langPath = __DIR__ . '/../Resources/lang';
        if (is_dir($langPath)) {
            Lang::addNamespace('finance', $langPath);
        }
    }
}
