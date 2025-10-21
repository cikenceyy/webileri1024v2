<?php

use App\Modules\Marketing\Http\Controllers\ActivityController;
use App\Modules\Marketing\Http\Controllers\AddressController;
use App\Modules\Marketing\Http\Controllers\AttachmentController;
use App\Modules\Marketing\Http\Controllers\ContactController;
use App\Modules\Marketing\Http\Controllers\CustomerController;
use App\Modules\Marketing\Http\Controllers\DemoController;
use App\Modules\Marketing\Http\Controllers\ImportController;
use App\Modules\Marketing\Http\Controllers\NoteController;
use App\Modules\Marketing\Http\Controllers\OpportunityController;
use App\Modules\Marketing\Http\Controllers\OrderController;
use App\Modules\Marketing\Http\Controllers\QuoteController;
use App\Modules\Marketing\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/marketing')
    ->as('admin.marketing.')
    ->group(function (): void {
        Route::get('/', [DemoController::class, 'index'])->name('index');

        Route::resource('customers', CustomerController::class)
            ->names('customers')
            ->parameters(['customers' => 'customer']);

        Route::get('customers/{customer}/show', [CustomerController::class, 'show'])->name('customers.show');

        Route::resource('customers.contacts', ContactController::class)
            ->shallow()
            ->only(['index', 'store', 'update', 'destroy'])
            ->names('contacts');

        Route::resource('customers.addresses', AddressController::class)
            ->shallow()
            ->only(['index', 'store', 'update', 'destroy'])
            ->names('addresses');

        Route::resource('opportunities', OpportunityController::class)->names('opportunities');

        Route::resource('quotes', QuoteController::class)->names('quotes');
        Route::get('quotes/{quote}/print', [QuoteController::class, 'print'])->name('quotes.print');

        Route::resource('orders', OrderController::class)->names('orders');
        Route::get('orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');

        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/sales/print', [ReportController::class, 'salesPrint'])->name('reports.sales.print');
        Route::get('reports/sales/export', [ReportController::class, 'salesExport'])->name('reports.sales.export');

        Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
        Route::post('activities', [ActivityController::class, 'store'])->name('activities.store');
        Route::put('activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');
        Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');

        Route::post('notes', [NoteController::class, 'store'])->name('notes.store');
        Route::delete('notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

        Route::post('attachments', [AttachmentController::class, 'store'])->name('attachments.store');
        Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

        Route::get('import/customers', [ImportController::class, 'form'])->name('customers.import.form');
        Route::post('import/customers', [ImportController::class, 'importCustomers'])->name('customers.import');
    });
