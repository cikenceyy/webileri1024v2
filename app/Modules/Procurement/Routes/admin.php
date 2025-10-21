<?php

use App\Modules\Procurement\Http\Controllers\GrnController;
use App\Modules\Procurement\Http\Controllers\PoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/procurement')
    ->name('admin.procurement.')
    ->group(function (): void {
        Route::resource('pos', PoController::class)
            ->only(['index', 'create', 'store', 'show', 'update'])
            ->parameter('pos', 'po');

        Route::get('grns', [GrnController::class, 'index'])->name('grns.index');
        Route::get('grns/create', [GrnController::class, 'create'])->name('grns.create');
        Route::post('grns', [GrnController::class, 'store'])->name('grns.store');
        Route::get('grns/{grn}', [GrnController::class, 'show'])->name('grns.show');
    });
