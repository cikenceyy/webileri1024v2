<?php

use App\Cms\Http\Controllers\Admin\ContactMessageController;
use App\Cms\Http\Controllers\Admin\PageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'permission:cms.manage'])->prefix('admin/cms')->as('cms.admin.')->group(function () {
    Route::get('/', [PageController::class, 'index'])->name('pages.index');
    Route::get('/{page}', [PageController::class, 'edit'])->name('pages.edit');
    Route::post('/{page}', [PageController::class, 'update'])->name('pages.update');

    Route::get('/messages', [ContactMessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{message}', [ContactMessageController::class, 'show'])->name('messages.show');
});
