<?php

use App\Modules\Marketing\Http\Controllers\Admin\CustomerController;
use App\Modules\Marketing\Http\Controllers\Admin\OrderController;
use App\Modules\Marketing\Http\Controllers\Admin\PricelistBulkController;
use App\Modules\Marketing\Http\Controllers\Admin\PricelistController;
use App\Modules\Marketing\Http\Controllers\Admin\ReturnController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/marketing')
    ->as('admin.marketing.')
    ->group(function (): void {
        Route::get('/', function () {
            return redirect()->route('admin.marketing.customers.index');
        })->name('index');

        Route::resource('customers', CustomerController::class)->except(['destroy']);

        Route::resource('orders', OrderController::class)->except(['destroy']);
        Route::post('orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

        Route::resource('returns', ReturnController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('returns/{return}/approve', [ReturnController::class, 'approve'])->name('returns.approve');
        Route::post('returns/{return}/close', [ReturnController::class, 'close'])->name('returns.close');

        Route::get('pricelists', [PricelistController::class, 'index'])->name('pricelists.index');
        Route::get('pricelists/{pricelist}', [PricelistController::class, 'show'])->name('pricelists.show');

        Route::get('pricelists/{pricelist}/bulk', [PricelistBulkController::class, 'form'])->name('pricelists.bulk.form');
        Route::post('pricelists/{pricelist}/bulk/preview', [PricelistBulkController::class, 'preview'])->name('pricelists.bulk.preview');
        Route::post('pricelists/{pricelist}/bulk/apply', [PricelistBulkController::class, 'apply'])->name('pricelists.bulk.apply');
    });
