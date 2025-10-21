<?php

use App\Consoles\Http\Controllers\MTOController;
use App\Consoles\Http\Controllers\O2CController;
use App\Consoles\Http\Controllers\P2PController;
use App\Consoles\Http\Controllers\TodayBoardController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin.consoles')
    ->as('consoles.')
    ->middleware(['web', 'auth', 'tenant'])
    ->group(static function (): void {
        Route::get('today', [TodayBoardController::class, 'index'])->name('today');

        Route::get('o2c', [O2CController::class, 'index'])->name('o2c');
        Route::post('o2c/execute/{step}', [O2CController::class, 'execute'])->name('o2c.execute');

        Route::get('p2p', [P2PController::class, 'index'])->name('p2p');
        Route::post('p2p/execute/{step}', [P2PController::class, 'execute'])->name('p2p.execute');

        Route::get('mto', [MTOController::class, 'index'])->name('mto');
        Route::post('mto/execute/{step}', [MTOController::class, 'execute'])->name('mto.execute');
    });
