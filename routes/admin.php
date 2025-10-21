<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->as('admin.')
    ->middleware(['tenant'])
    ->group(function (): void {
        Route::middleware('guest')->group(function (): void {
            Route::get('login', [LoginController::class, 'showLoginForm'])
                ->name('auth.login.show');

            Route::post('login', [LoginController::class, 'login'])
                ->middleware('throttle:10,1')
                ->name('auth.login.attempt');
        });

        Route::middleware('auth')->group(function (): void {
            Route::get('/', DashboardController::class)->name('dashboard');
            Route::post('logout', [LoginController::class, 'logout'])->name('auth.logout');
        });
    });
