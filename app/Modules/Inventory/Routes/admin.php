<?php

/**
 * Inventory modülünün admin rotaları: stok konsolu ve klasik CRUD uçları.
 */

use App\Modules\Inventory\Http\Controllers\HomeController;
use App\Modules\Inventory\Http\Controllers\CategoryController;
use App\Modules\Inventory\Http\Controllers\ProductController;
use App\Modules\Inventory\Http\Controllers\SettingsController;
use App\Modules\Inventory\Http\Controllers\StockConsoleController;
use App\Modules\Inventory\Http\Controllers\StockCountController;
use App\Modules\Inventory\Http\Controllers\StockTransferController;
use App\Modules\Inventory\Http\Controllers\WarehouseBinController;
use App\Modules\Inventory\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/inventory')
    ->name('admin.inventory.')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('home/metrics', [HomeController::class, 'metrics'])->name('home.metrics');
        Route::get('home/timeline', [HomeController::class, 'timeline'])->name('home.timeline');
        Route::get('home/lowstock', [HomeController::class, 'lowstock'])->name('home.lowstock');

        Route::get('stock/console', [StockConsoleController::class, 'index'])->name('stock.console');
        Route::get('stock/console/grid', [StockConsoleController::class, 'grid'])
            ->middleware('fresh')
            ->name('stock.console.grid');
        Route::post('stock/console', [StockConsoleController::class, 'store'])->name('stock.console.store');
        Route::get('stock/lookup', [StockConsoleController::class, 'lookup'])->name('stock.console.lookup');

        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::get('products/{product}/components', [ProductController::class, 'components'])->name('products.components');

        Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');
        Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');

        Route::post('warehouses/{warehouse}/bins', [WarehouseBinController::class, 'store'])->name('warehouses.bins.store');
        Route::put('warehouses/{warehouse}/bins/{bin}', [WarehouseBinController::class, 'update'])->name('warehouses.bins.update');
        Route::delete('warehouses/{warehouse}/bins/{bin}', [WarehouseBinController::class, 'destroy'])->name('warehouses.bins.destroy');

        Route::get('transfers', [StockTransferController::class, 'index'])->name('transfers.index');
        Route::get('transfers/create', [StockTransferController::class, 'create'])->name('transfers.create');
        Route::post('transfers', [StockTransferController::class, 'store'])->name('transfers.store');
        Route::get('transfers/{transfer}', [StockTransferController::class, 'show'])->name('transfers.show');
        Route::post('transfers/{transfer}/post', [StockTransferController::class, 'post'])
            ->middleware('idempotency')
            ->name('transfers.post');

        Route::get('counts', [StockCountController::class, 'index'])->name('counts.index');
        Route::get('counts/create', [StockCountController::class, 'create'])->name('counts.create');
        Route::post('counts', [StockCountController::class, 'store'])->name('counts.store');
        Route::get('counts/{count}', [StockCountController::class, 'show'])->name('counts.show');
        Route::patch('counts/{count}/mark-counted', [StockCountController::class, 'markCounted'])->name('counts.mark-counted');
        Route::patch('counts/{count}/reconcile', [StockCountController::class, 'reconcile'])
            ->middleware('idempotency')
            ->name('counts.reconcile');

        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    });
