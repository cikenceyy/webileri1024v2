<?php

use App\Modules\Inventory\Http\Controllers\CategoryController;
use App\Modules\Inventory\Http\Controllers\ImportController;
use App\Modules\Inventory\Http\Controllers\PriceListController;
use App\Modules\Inventory\Http\Controllers\ProductController;
use App\Modules\Inventory\Http\Controllers\VariantController;
use App\Modules\Inventory\Http\Controllers\WarehouseController;
use App\Modules\Inventory\Http\Controllers\UnitController;
use App\Modules\Inventory\Http\Controllers\StockController;
use App\Modules\Inventory\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/inventory')
    ->name('admin.inventory.')
    ->group(function () {
        Route::resource('categories', CategoryController::class)->names('categories');
        Route::resource('units', UnitController::class)->only(['index', 'create', 'store'])->names('units');
        Route::resource('pricelists', PriceListController::class)->names('pricelists');
        Route::post('pricelists/{pricelist}/items', [PriceListController::class, 'storeItem'])->name('pricelists.items.store');
        Route::delete('pricelists/{pricelist}/items/{item}', [PriceListController::class, 'destroyItem'])->name('pricelists.items.destroy');
        Route::resource('warehouses', WarehouseController::class)->names('warehouses');

        Route::resource('products', ProductController::class)->names('products');
        Route::post('products/{product}/gallery', [ProductController::class, 'addGallery'])->name('products.gallery.add');
        Route::delete('products/{product}/gallery/{gallery}', [ProductController::class, 'removeGallery'])->name('products.gallery.remove');

        Route::prefix('products/{product}')
            ->name('products.')
            ->group(function () {
                Route::resource('variants', VariantController::class)->names('variants');
            });

        Route::get('import/products/sample', [ImportController::class, 'sample'])->name('import.products.sample');
        Route::get('import/products', [ImportController::class, 'form'])->name('import.products.form');
        Route::post('import/products', [ImportController::class, 'store'])->name('import.products.store');

        Route::get('stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('stock/in', [StockController::class, 'inForm'])->name('stock.in.form');
        Route::post('stock/in', [StockController::class, 'storeIn'])->name('stock.in.store');
        Route::get('stock/out', [StockController::class, 'outForm'])->name('stock.out.form');
        Route::post('stock/out', [StockController::class, 'storeOut'])->name('stock.out.store');
        Route::get('stock/transfer', [StockController::class, 'transferForm'])->name('stock.transfer.form');
        Route::post('stock/transfer', [StockController::class, 'storeTransfer'])->name('stock.transfer.store');
        Route::get('stock/adjust', [StockController::class, 'adjustForm'])->name('stock.adjust.form');
        Route::post('stock/adjust', [StockController::class, 'storeAdjust'])->name('stock.adjust.store');

        Route::get('reports/onhand', [ReportController::class, 'onHand'])->name('reports.onhand');
        Route::get('reports/ledger', [ReportController::class, 'ledger'])->name('reports.ledger');
        Route::get('reports/valuation', [ReportController::class, 'valuation'])->name('reports.valuation');
    });
