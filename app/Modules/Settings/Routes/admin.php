<?php

use App\Modules\Settings\Http\Controllers\CompanyController;
use App\Modules\Settings\Http\Controllers\CompanyDomainController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/settings')
    ->as('admin.settings.')
    ->group(function (): void {
        Route::get('/company', [CompanyController::class, 'edit'])
            ->name('company.edit');
        Route::put('/company', [CompanyController::class, 'update'])
            ->name('company.update');

        Route::post('/company/domains', [CompanyDomainController::class, 'store'])
            ->name('company.domains.store');
        Route::post('/company/domains/{domain}/make-primary', [CompanyDomainController::class, 'makePrimary'])
            ->name('company.domains.make_primary');
        Route::delete('/company/domains/{domain}', [CompanyDomainController::class, 'destroy'])
            ->name('company.domains.destroy');
    });
