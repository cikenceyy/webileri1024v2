<?php

namespace App\Modules\Inventory\Providers;

use App\Modules\Inventory\Console\Commands\RebuildOnHand;
use App\Modules\Inventory\Domain\Models\PriceList;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductCategory;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use App\Modules\Inventory\Domain\Models\StockMovement;
use App\Modules\Inventory\Domain\Models\Unit;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Policies\CategoryPolicy;
use App\Modules\Inventory\Policies\PriceListPolicy;
use App\Modules\Inventory\Policies\ProductPolicy;
use App\Modules\Inventory\Policies\StockPolicy;
use App\Modules\Inventory\Policies\UnitPolicy;
use App\Modules\Inventory\Policies\VariantPolicy;
use App\Modules\Inventory\Policies\WarehousePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/inventory.php', 'inventory');
        $this->mergeConfigFrom(__DIR__ . '/../Config/permissions.php', 'inventory-permissions');

        if ($this->app->runningInConsole()) {
            $this->commands([RebuildOnHand::class]);
        }
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'inventory');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');

        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(ProductCategory::class, CategoryPolicy::class);
        Gate::policy(Unit::class, UnitPolicy::class);
        Gate::policy(PriceList::class, PriceListPolicy::class);
        Gate::policy(Warehouse::class, WarehousePolicy::class);
        Gate::policy(ProductVariant::class, VariantPolicy::class);
        Gate::policy(StockMovement::class, StockPolicy::class);
    }
}
