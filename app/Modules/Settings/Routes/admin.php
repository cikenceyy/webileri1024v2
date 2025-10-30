<?php

use App\Modules\Settings\Domain\Models\Setting;
use App\Modules\Settings\Http\Controllers\Admin\CacheController;
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

        Route::get('/cache', [CacheController::class, 'index'])
            ->name('cache.index')
            ->middleware('can:update,' . Setting::class);

        Route::post('/cache/warm', [CacheController::class, 'warm'])
            ->name('cache.warm')
            ->middleware('can:update,' . Setting::class);

        Route::post('/cache/flush', [CacheController::class, 'flush'])
            ->name('cache.flush')
            ->middleware('can:update,' . Setting::class);
    });
