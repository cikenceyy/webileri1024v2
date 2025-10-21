<?php

use App\Modules\Finance\Http\Controllers\AllocationController;
use App\Modules\Finance\Http\Controllers\ApInvoiceController;
use App\Modules\Finance\Http\Controllers\ApPaymentController;
use App\Modules\Finance\Http\Controllers\BankAccountController;
use App\Modules\Finance\Http\Controllers\BankTransactionController;
use App\Modules\Finance\Http\Controllers\InvoiceController;
use App\Modules\Finance\Http\Controllers\ReceiptController;
use App\Modules\Finance\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/finance')
    ->name('admin.finance.')
    ->group(function (): void {
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::resource('invoices', InvoiceController::class);
        Route::get('invoices/from-order/{order}', [InvoiceController::class, 'createFromOrder'])->name('invoices.from-order');

        Route::resource('receipts', ReceiptController::class);

        Route::get('allocations', [AllocationController::class, 'index'])->name('allocations.index');
        Route::post('allocations', [AllocationController::class, 'store'])->name('allocations.store');
        Route::delete('allocations/{allocation}', [AllocationController::class, 'destroy'])->name('allocations.destroy');

        Route::resource('bank-accounts', BankAccountController::class)->except(['show']);
        Route::get('bank-transactions', [BankTransactionController::class, 'index'])->name('bank-transactions.index');
        Route::post('bank-transactions', [BankTransactionController::class, 'store'])->name('bank-transactions.store');
        Route::delete('bank-transactions/{bankTransaction}', [BankTransactionController::class, 'destroy'])->name('bank-transactions.destroy');

        Route::get('reports/aging', [ReportController::class, 'aging'])->name('reports.aging');
        Route::get('reports/receipts', [ReportController::class, 'receipts'])->name('reports.receipts');
        Route::get('reports/summary', [ReportController::class, 'summary'])->name('reports.summary');

        Route::resource('ap-invoices', ApInvoiceController::class)->only(['index', 'show', 'update']);
        Route::resource('ap-payments', ApPaymentController::class)->only(['index', 'create', 'store']);
    });
