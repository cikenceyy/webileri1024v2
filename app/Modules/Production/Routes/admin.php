<?php

use App\Modules\Production\Http\Controllers\Admin\BomController;
use App\Modules\Production\Http\Controllers\Admin\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/production')
    ->name('admin.production.')
    ->group(function (): void {
        Route::resource('boms', BomController::class)
            ->parameter('boms', 'bom')
            ->names('boms');
        Route::post('boms/{bom}/duplicate', [BomController::class, 'duplicate'])->name('boms.duplicate');

        Route::resource('workorders', WorkOrderController::class)
            ->parameter('workorders', 'workOrder')
            ->names('workorders')
            ->except(['destroy']);

        Route::post('workorders/{workOrder}/release', [WorkOrderController::class, 'release'])->name('workorders.release');
        Route::post('workorders/{workOrder}/start', [WorkOrderController::class, 'start'])->name('workorders.start');
        Route::post('workorders/{workOrder}/issue', [WorkOrderController::class, 'issue'])
            ->middleware('idempotency')
            ->name('workorders.issue');
        Route::post('workorders/{workOrder}/complete', [WorkOrderController::class, 'complete'])
            ->middleware('idempotency')
            ->name('workorders.complete');
        Route::post('workorders/{workOrder}/close', [WorkOrderController::class, 'close'])->name('workorders.close');
        Route::post('workorders/{workOrder}/cancel', [WorkOrderController::class, 'cancel'])->name('workorders.cancel');
    });
