<?php

use App\Modules\HR\Http\Controllers\Admin\DepartmentController;
use App\Modules\HR\Http\Controllers\Admin\EmployeeController;
use App\Modules\HR\Http\Controllers\Admin\EmploymentTypeController;
use App\Modules\HR\Http\Controllers\Admin\TitleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant', 'auth', 'verified'])
    ->prefix('/admin/hr')
    ->as('admin.hr.')
    ->group(function (): void {
        Route::get('/', function () {
            return redirect()->route('admin.hr.employees.index');
        })->name('index');

        Route::prefix('settings')->as('settings.')->group(function (): void {
            Route::resource('departments', DepartmentController::class)->except(['show']);
            Route::resource('titles', TitleController::class)->except(['show']);
            Route::resource('employment-types', EmploymentTypeController::class)->except(['show']);
        });

        Route::resource('employees', EmployeeController::class)->except(['destroy']);
        Route::post('employees/{employee}/archive', [EmployeeController::class, 'archive'])->name('employees.archive');
    });
