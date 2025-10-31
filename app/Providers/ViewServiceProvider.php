<?php

namespace App\Providers;

use App\Core\Http\View\Composers\TableKitComposer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Blade::component('components.tablekit.table', 'table');
        Blade::component('components.tablekit.toolbar', 'table:toolbar');
        Blade::component('components.tablekit.col', 'table:col');
        Blade::component('components.tablekit.row-actions', 'table:row-actions');
        Blade::component('components.tablekit.stepper-summary', 'table:stepper-summary');
        Blade::component('components.tablekit.bulk', 'table:bulk');
        Blade::component('components.tablekit.row-meta', 'table:row-meta');

        Blade::if('canp', function (string $permission): bool {
            $user = auth()->user();

            if (! $user) {
                return false;
            }

            if (method_exists($user, 'hasPermissionTo') && class_exists(\Spatie\Permission\Models\Permission::class)) {
                return $user->hasPermissionTo($permission);
            }

            return true;
        });

        View::composer([
            'inventory::warehouses.index',
            'inventory::transfers.index',
            'inventory::counts.index',
            'inventory::products.index',
            'finance::admin.invoices.index',
            'finance::admin.receipts.index',
            'finance::admin.cashbook.index',
            'logistics::shipments.index',
            'logistics::receipts.index',
            'logistics::admin.shipments.index',
            'logistics::admin.receipts.index',
            'marketing::customers.index',
            'marketing::orders.index',
            'marketing::admin.customers.index',
            'marketing::admin.orders.index',
            'procurement::pos.index',
            'procurement::admin.pos.index',
            'procurement::grns.index',
            'procurement::admin.grns.index',
            'hr::admin.employees.index',
            'production::admin.workorders.index',
            'production::admin.boms.index',
        ], TableKitComposer::class);
    }
}
