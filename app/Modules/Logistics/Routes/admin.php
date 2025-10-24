<?php

use App\Modules\Logistics\Http\Controllers\Admin\ReceiptController;
use App\Modules\Logistics\Http\Controllers\Admin\ShipmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/logistics')
    ->name('admin.logistics.')
    ->group(function (): void {
        Route::get('shipments/{shipment}/print', [ShipmentController::class, 'print'])->name('shipments.print');
        Route::post('shipments/{shipment}/start-picking', [ShipmentController::class, 'startPicking'])->name('shipments.startPicking');
        Route::post('shipments/{shipment}/pick', [ShipmentController::class, 'pick'])->name('shipments.pick');
        Route::post('shipments/{shipment}/pack', [ShipmentController::class, 'pack'])->name('shipments.pack');
        Route::post('shipments/{shipment}/ship', [ShipmentController::class, 'ship'])
            ->middleware('idempotency')
            ->name('shipments.ship');
        Route::post('shipments/{shipment}/close', [ShipmentController::class, 'close'])->name('shipments.close');
        Route::post('shipments/{shipment}/cancel', [ShipmentController::class, 'cancel'])->name('shipments.cancel');
        Route::resource('shipments', ShipmentController::class)->except(['destroy']);

        Route::get('receipts/{receipt}/print', [ReceiptController::class, 'print'])->name('receipts.print');
        Route::post('receipts/{receipt}/receive', [ReceiptController::class, 'receive'])
            ->middleware('idempotency')
            ->name('receipts.receive');
        Route::post('receipts/{receipt}/reconcile', [ReceiptController::class, 'reconcile'])
            ->middleware('idempotency')
            ->name('receipts.reconcile');
        Route::post('receipts/{receipt}/close', [ReceiptController::class, 'close'])->name('receipts.close');
        Route::post('receipts/{receipt}/cancel', [ReceiptController::class, 'cancel'])->name('receipts.cancel');
        Route::resource('receipts', ReceiptController::class)->except(['destroy']);
    });
