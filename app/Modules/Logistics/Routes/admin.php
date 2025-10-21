<?php

use App\Modules\Logistics\Http\Controllers\ReportController;
use App\Modules\Logistics\Http\Controllers\ShipmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/logistics')
    ->name('admin.logistics.')
    ->group(function (): void {
        Route::get('shipments/{shipment}/print', [ShipmentController::class, 'print'])->name('shipments.print');
        Route::resource('shipments', ShipmentController::class)
            ->names('shipments')
            ->parameters(['shipments' => 'shipment']);

        Route::get('reports/register', [ReportController::class, 'register'])->name('reports.register');
    });
