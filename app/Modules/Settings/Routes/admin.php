<?php

use App\Modules\Settings\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/settings')
    ->as('admin.settings.')
    ->group(function (): void {
        Route::get('/company', [CompanyController::class, 'edit'])
            ->name('company.edit');
        Route::put('/company', [CompanyController::class, 'update'])
            ->name('company.update');
    });
