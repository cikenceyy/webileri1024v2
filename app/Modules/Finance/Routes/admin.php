<?php

use App\Modules\Finance\Http\Controllers\AllocationController;
use App\Modules\Finance\Http\Controllers\BankTransactionController;
use App\Modules\Finance\Http\Controllers\CashPanelController;
use App\Modules\Finance\Http\Controllers\CollectionConsoleController;
use App\Modules\Finance\Http\Controllers\FinanceHomeController;
use App\Modules\Finance\Http\Controllers\InvoiceController;
use App\Modules\Finance\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/finance')
    ->name('admin.finance.')
    ->group(function (): void {
        Route::get('/', FinanceHomeController::class)->name('home');

        if (config('features.finance.collections_console')) {
            Route::get('collections', [CollectionConsoleController::class, 'index'])->name('collections.index');
            Route::get('collections/invoices/{invoice}', [CollectionConsoleController::class, 'show'])
                ->name('collections.show');
            Route::put('collections/invoices/{invoice}/lane', [CollectionConsoleController::class, 'updateLane'])
                ->name('collections.lane');
        }

        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::get('invoices/from-order/{order}', [InvoiceController::class, 'createFromOrder'])->name('invoices.from-order');
        Route::resource('invoices', InvoiceController::class);

        Route::resource('receipts', ReceiptController::class)->only(['index', 'create', 'store', 'show']);

        Route::post('allocations', [AllocationController::class, 'store'])->name('allocations.store');
        Route::delete('allocations/{allocation}', [AllocationController::class, 'destroy'])->name('allocations.destroy');

        Route::get('cash-panel', [CashPanelController::class, 'index'])->name('cash-panel.index');
        Route::post('cash-panel/accounts', [CashPanelController::class, 'storeAccount'])->name('cash-panel.accounts.store');
        Route::patch('cash-panel/accounts/{account}', [CashPanelController::class, 'updateAccount'])
            ->name('cash-panel.accounts.update');
        Route::delete('cash-panel/accounts/{account}', [CashPanelController::class, 'destroyAccount'])
            ->name('cash-panel.accounts.destroy');
        Route::post('cash-panel/transactions', [CashPanelController::class, 'storeTransaction'])
            ->name('cash-panel.transactions.store');
        Route::delete('cash-panel/transactions/{transaction}', [CashPanelController::class, 'destroyTransaction'])
            ->name('cash-panel.transactions.destroy');
        Route::post('cash-panel/transactions/import', [CashPanelController::class, 'importTransactions'])
            ->name('cash-panel.transactions.import');

        Route::get('transactions', [BankTransactionController::class, 'index'])->name('transactions.index');
    });
