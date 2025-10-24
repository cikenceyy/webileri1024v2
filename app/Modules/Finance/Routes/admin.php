<?php

use App\Modules\Finance\Http\Controllers\Admin\CashbookController;
use App\Modules\Finance\Http\Controllers\Admin\InvoiceController;
use App\Modules\Finance\Http\Controllers\Admin\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/finance')
    ->name('admin.finance.')
    ->group(function (): void {
        Route::get('/', [InvoiceController::class, 'index'])->name('home');

        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::post('invoices/{invoice}/issue', [InvoiceController::class, 'issue'])
            ->middleware('idempotency')
            ->name('invoices.issue');
        Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
        Route::get('invoices/from-order/{order}', [InvoiceController::class, 'createFromOrder'])->name('invoices.from-order');
        Route::resource('invoices', InvoiceController::class);

        Route::get('receipts/{receipt}/apply', [ReceiptController::class, 'applyForm'])->name('receipts.apply-form');
        Route::post('receipts/{receipt}/apply', [ReceiptController::class, 'apply'])
            ->middleware('idempotency')
            ->name('receipts.apply');
        Route::resource('receipts', ReceiptController::class)->only(['index', 'create', 'store', 'show']);

        Route::resource('cashbook', CashbookController::class)->only(['index', 'create', 'store', 'show']);
    });
