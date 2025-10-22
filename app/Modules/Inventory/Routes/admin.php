<?php

use App\Modules\Inventory\Http\Controllers\BomController;
use App\Modules\Inventory\Http\Controllers\HomeController;
use App\Modules\Inventory\Http\Controllers\PricelistController;
use App\Modules\Inventory\Http\Controllers\ProductController;
use App\Modules\Inventory\Http\Controllers\SettingsController;
use App\Modules\Inventory\Http\Controllers\StockConsoleController;
use App\Modules\Inventory\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/inventory')
    ->name('admin.inventory.')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::get('stock/console', [StockConsoleController::class, 'index'])->name('stock.console');
        Route::post('stock/console', [StockConsoleController::class, 'store'])->name('stock.console.store');
        Route::get('stock/lookup', [StockConsoleController::class, 'lookup'])->name('stock.console.lookup');

        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::get('products/{product}/components', [ProductController::class, 'components'])->name('products.components');

        Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');

        Route::get('pricelists', [PricelistController::class, 'index'])->name('pricelists.index');
        Route::get('pricelists/{pricelist}', [PricelistController::class, 'show'])->name('pricelists.show');

        Route::get('bom', [BomController::class, 'index'])->name('bom.index');
        Route::get('bom/{product}', [BomController::class, 'show'])->name('bom.show');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    });
