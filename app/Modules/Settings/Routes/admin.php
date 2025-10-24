<?php

use App\Modules\Settings\Domain\Models\Setting;
use App\Modules\Settings\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/settings')
    ->as('admin.settings.')
    ->group(function (): void {
        Route::get('/', [SettingsController::class, 'index'])
            ->name('index')
            ->middleware('can:viewAny,' . Setting::class);

        Route::post('/', [SettingsController::class, 'store'])
            ->name('store')
            ->middleware('can:update,' . Setting::class);
    });
