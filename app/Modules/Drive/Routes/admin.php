<?php

use App\Modules\Drive\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/drive')
    ->name('admin.drive.media.')
    ->group(function (): void {
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::post('/upload', [MediaController::class, 'store'])->name('store');
        Route::post('/upload-many', [MediaController::class, 'storeMany'])->name('store_many');
        Route::post('/{media}/replace', [MediaController::class, 'replace'])->name('replace');
        Route::get('/{media}/download', [MediaController::class, 'download'])->name('download');
        Route::delete('/{media}', [MediaController::class, 'destroy'])->name('destroy');
        Route::post('/{media}/toggle-important', [MediaController::class, 'toggleImportant'])->name('toggle_important');
    });
