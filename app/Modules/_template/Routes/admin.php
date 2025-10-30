<?php

use Illuminate\Support\Facades\Route;

/**
 * Modül şablonunun yönetici rotaları.
 * Kopyaladıktan sonra namespace ve middleware yapılarını gerçek modül
 * gereksinimlerinize göre güncelleyin.
 */
Route::middleware(['web', 'auth', 'verified'])
    ->prefix('admin/example')
    ->name('admin.example.')
    ->group(static function () {
        Route::get('/', [\App\Modules\_template\Http\Controllers\Admin\ExampleController::class, 'index'])
            ->name('index');
    });
