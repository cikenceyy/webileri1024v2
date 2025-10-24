<?php

namespace App\Consoles\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');

        $viewsPath = __DIR__ . '/../Resources/views';
        $this->loadViewsFrom($viewsPath, 'consoles');

        Gate::define('viewO2CConsole', static function ($user): bool {
            return $user->can('marketing.orders.view')
                && $user->can('logistics.shipments.view')
                && $user->can('finance.invoices.view')
                && $user->can('finance.receipts.view');
        });

        Gate::define('viewP2PConsole', static function ($user): bool {
            return $user->can('procurement.view')
                && $user->can('logistics.receipts.view')
                && $user->can('finance.invoices.view');
        });

        Gate::define('viewMTOConsole', static function ($user): bool {
            return $user->can('production.workorders.view');
        });

        Gate::define('viewReplenishConsole', static function ($user): bool {
            return $user->can('inventory.transfers.view');
        });

        Gate::define('viewReturnsConsole', static function ($user): bool {
            return $user->can('marketing.returns.view');
        });

        Gate::define('viewQualityConsole', static function ($user): bool {
            return $user->can('logistics.shipments.view') || $user->can('logistics.receipts.view');
        });

        Gate::define('viewCloseoutConsole', static function ($user): bool {
            return $user->can('finance.invoices.print') || $user->can('finance.receipts.view');
        });
    }
}
