<?php

namespace App\Modules\Finance\Providers;

use App\Modules\Finance\Console\Commands\RebuildAccountsReceivable;
use App\Modules\Finance\Domain\Models\Allocation;
use App\Modules\Finance\Domain\Models\BankAccount;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use App\Modules\Finance\Policies\AllocationPolicy;
use App\Modules\Finance\Policies\BankAccountPolicy;
use App\Modules\Finance\Policies\ArInvoicePolicy;
use App\Modules\Finance\Policies\ReceiptPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/finance.php', 'finance');
        $this->mergeConfigFrom(__DIR__ . '/../Config/permissions.php', 'finance-permissions');

        if ($this->app->runningInConsole()) {
            $this->commands([RebuildAccountsReceivable::class]);
        }
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'finance');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');

        Gate::policy(Invoice::class, ArInvoicePolicy::class);
        Gate::policy(Receipt::class, ReceiptPolicy::class);
        Gate::policy(Allocation::class, AllocationPolicy::class);
        Gate::policy(BankAccount::class, BankAccountPolicy::class);
    }
}
