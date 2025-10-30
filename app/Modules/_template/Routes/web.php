<?php

use Illuminate\Support\Facades\Route;

/**
 * Modül şablonunun genel (public) rotaları.
 * Gerekirse middleware, prefix ve isim alanlarını modül gereksinimlerinize
 * göre düzenleyin.
 */
Route::middleware('web')
    ->prefix('example')
    ->name('example.')
    ->group(static function () {
        Route::get('/', static fn () => view('example::welcome'))->name('index');
    });
