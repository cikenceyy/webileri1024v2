<?php

use App\Modules\Production\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/production')
    ->name('admin.production.')
    ->group(function (): void {
        Route::resource('work-orders', WorkOrderController::class)
            ->only(['index', 'store', 'show', 'update'])
            ->parameter('work-orders', 'workOrder')
            ->names('work-orders');

        Route::patch('work-orders/{workOrder}/close', [WorkOrderController::class, 'close'])
            ->name('work-orders.close');
    });
