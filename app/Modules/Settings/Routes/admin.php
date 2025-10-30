<?php

use App\Modules\Settings\Domain\Models\Setting;
use App\Modules\Settings\Http\Controllers\Admin\CacheController;
use App\Modules\Settings\Http\Controllers\Admin\DiagnosticsController;
use App\Modules\Settings\Http\Controllers\Admin\EmailSettingsController;
use App\Modules\Settings\Http\Controllers\Admin\GeneralSettingsController;
use App\Modules\Settings\Http\Controllers\Admin\ModuleSettingsController;
use Illuminate\Support\Facades\Route;

// Önbellek yönetimi sayfası yalnızca admin/superadmin (SettingsPolicy update yetkisi) tarafından erişilir.
Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/settings')
    ->as('admin.settings.')
    ->group(function (): void {
        Route::redirect('/', '/admin/settings/general')
            ->name('index')
            ->middleware('can:update,' . Setting::class);

        Route::get('/general', [GeneralSettingsController::class, 'show'])
            ->name('general.show')
            ->middleware('can:update,' . Setting::class);

        Route::post('/general', [GeneralSettingsController::class, 'update'])
            ->name('general.update')
            ->middleware('can:update,' . Setting::class);

        Route::get('/email', [EmailSettingsController::class, 'show'])
            ->name('email.show')
            ->middleware('can:update,' . Setting::class);

        Route::post('/email', [EmailSettingsController::class, 'update'])
            ->name('email.update')
            ->middleware('can:update,' . Setting::class);

        Route::post('/email/test', [EmailSettingsController::class, 'sendTest'])
            ->name('email.test')
            ->middleware('can:update,' . Setting::class);

        Route::get('/modules', [ModuleSettingsController::class, 'show'])
            ->name('modules.show')
            ->middleware('can:update,' . Setting::class);

        Route::post('/modules', [ModuleSettingsController::class, 'update'])
            ->name('modules.update')
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

        Route::get('/diagnostics', [DiagnosticsController::class, 'index'])
            ->name('diagnostics.index')
            ->middleware('can:update,' . Setting::class);
    });
