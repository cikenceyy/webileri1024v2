<?php

use App\Consoles\Http\Controllers\MTOController;
use App\Consoles\Http\Controllers\O2CController;
use App\Consoles\Http\Controllers\P2PController;
use App\Consoles\Http\Controllers\TodayBoardController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UIController;
use Illuminate\Support\Facades\Route;

Route::middleware(['tenant', 'auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::middleware('guest')
            ->withoutMiddleware(['auth', 'verified'])
            ->group(function (): void {
                Route::get('/login', [LoginController::class, 'showLoginForm'])->name('auth.login.show');
                Route::post('/login', [LoginController::class, 'login'])
                    ->middleware('throttle:10,1')
                    ->name('auth.login.attempt');
            });

        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/today-board', [TodayBoardController::class, 'index'])->name('today-board');
        Route::get('/consoles/mto', [MTOController::class, 'index'])->name('consoles.mto');
        Route::post('/consoles/mto/orders/{order}/work-orders', function () {
            return redirect()->route('consoles.mto')
                ->with('info', __('MTO aksiyonları yeni konsol ekranına taşındı.'));
        })->name('consoles.mto.work-orders.store');
        Route::post('/consoles/mto/shipments/{shipment}/ship', function () {
            return redirect()->route('consoles.o2c')
                ->with('info', __('Sevkiyat aksiyonları O2C konsoluna taşındı.'));
        })->name('consoles.mto.shipments.ship');
        Route::get('/consoles/p2p', [P2PController::class, 'index'])->name('consoles.p2p');
        Route::post('/consoles/p2p/invoices/{invoice}/collect', function () {
            return redirect()->route('consoles.p2p')
                ->with('info', __('Tahsilat aksiyonları yeni P2P konsoluna taşındı.'));
        })->name('consoles.p2p.invoices.collect');
        Route::post('/consoles/p2p/stock-items/{stockItem}/purchase-orders', function () {
            return redirect()->route('consoles.p2p')
                ->with('info', __('Satın alma aksiyonları yeni P2P konsoluna taşındı.'));
        })->name('consoles.p2p.stock-items.purchase-orders');
        Route::get('/consoles/o2c', [O2CController::class, 'index'])->name('consoles.o2c');
        Route::post('/consoles/o2c/execute/{step}', [O2CController::class, 'execute'])->name('consoles.o2c.execute');
        Route::get('/ui', [UIController::class, 'index'])
            ->middleware('http.cache:180')
            ->name('ui.index');
        Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout');
    });
