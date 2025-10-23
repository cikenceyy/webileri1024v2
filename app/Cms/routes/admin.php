<?php

use App\Cms\Http\Controllers\Admin\ContactMessageController;
use App\Cms\Http\Controllers\Admin\EditorController;
use App\Cms\Http\Controllers\Admin\PageController;
use App\Cms\Http\Controllers\Admin\PreviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'permission:cms.manage'])->prefix('admin/cms')->as('cms.admin.')->group(function () {
    Route::get('/editor', [EditorController::class, 'index'])->name('editor');
    Route::post('/save', [EditorController::class, 'save'])->name('save');
    Route::post('/preview/apply', [PreviewController::class, 'apply'])->name('preview.apply');
    Route::post('/preview/discard', [PreviewController::class, 'discard'])->name('preview.discard');
    Route::post('/preview/upload', [PreviewController::class, 'upload'])->name('preview.upload');

    Route::get('/', [PageController::class, 'index'])->name('pages.index');
    Route::get('/{page}', [PageController::class, 'edit'])
        ->whereIn('page', array_keys(config('cms.pages')))
        ->name('pages.edit');
    Route::post('/{page}', [PageController::class, 'update'])
        ->whereIn('page', array_keys(config('cms.pages')))
        ->name('pages.update');

    Route::get('/messages', [ContactMessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{message}', [ContactMessageController::class, 'show'])->name('messages.show');
    Route::patch('/messages/{message}', [ContactMessageController::class, 'update'])->name('messages.update');
});
