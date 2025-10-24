<?php

use App\Consoles\Http\Controllers\CloseoutController;
use App\Consoles\Http\Controllers\MTOController;
use App\Consoles\Http\Controllers\O2CController;
use App\Consoles\Http\Controllers\P2PController;
use App\Consoles\Http\Controllers\QualityController;
use App\Consoles\Http\Controllers\ReplenishController;
use App\Consoles\Http\Controllers\ReturnsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/consoles')
    ->as('admin.consoles.')
    ->middleware(['web', 'auth', 'tenant'])
    ->group(static function (): void {
        Route::get('o2c', [O2CController::class, 'index'])->name('o2c.index');
        Route::post('o2c/action', [O2CController::class, 'action'])->name('o2c.action');

        Route::get('p2p', [P2PController::class, 'index'])->name('p2p.index');
        Route::post('p2p/action', [P2PController::class, 'action'])->name('p2p.action');

        Route::get('mto', [MTOController::class, 'index'])->name('mto.index');
        Route::post('mto/action', [MTOController::class, 'action'])->name('mto.action');

        Route::get('replenish', [ReplenishController::class, 'index'])->name('replenish.index');
        Route::post('replenish/transfer', [ReplenishController::class, 'createTransfer'])->name('replenish.transfer');

        Route::get('returns', [ReturnsController::class, 'index'])->name('returns.index');
        Route::post('returns/action', [ReturnsController::class, 'action'])->name('returns.action');

        Route::get('quality', [QualityController::class, 'index'])->name('quality.index');
        Route::post('quality/record', [QualityController::class, 'record'])->name('quality.record');

        Route::get('closeout', [CloseoutController::class, 'index'])->name('closeout.index');
        Route::post('closeout/print', [CloseoutController::class, 'batchPrint'])->name('closeout.print');
    });
