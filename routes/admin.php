<?php

use App\Core\Auth\Http\Controllers\AuthAuditController;
use App\Core\Bulk\Http\Controllers\BulkJobController;
use App\Core\Exports\Http\Controllers\ExportController as TableExportController;
use App\Core\Reports\Http\Controllers\ReportController;
use App\Core\TableKit\Http\Controllers\MetricsController;
use App\Core\TableKit\Http\Controllers\SavedFilterController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Modules\Settings\Domain\Models\Setting;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->as('admin.')
    ->middleware(['tenant'])
    ->group(function (): void {
        Route::middleware('guest')->group(function (): void {
            Route::get('login', [LoginController::class, 'showLoginForm'])
                ->name('auth.login.show');

            Route::post('login', [LoginController::class, 'login'])
                ->middleware('throttle:10,1')
                ->name('auth.login.attempt');
        });

        Route::middleware('auth')->group(function (): void {
            Route::get('/', DashboardController::class)->name('dashboard');
            Route::post('logout', [LoginController::class, 'logout'])->name('auth.logout');

            Route::prefix('tablekit')->as('tablekit.')->group(function (): void {
                Route::get('filters/{tableKey}', [SavedFilterController::class, 'index'])->name('filters.index');
                Route::post('filters/{tableKey}', [SavedFilterController::class, 'store'])->name('filters.store');
                Route::delete('filters/{tableKey}/{filter}', [SavedFilterController::class, 'destroy'])->name('filters.destroy');
                Route::post('filters/{tableKey}/{filter}/default', [SavedFilterController::class, 'makeDefault'])->name('filters.default');
            });

            Route::prefix('exports')->as('exports.')->group(function (): void {
                Route::get('/', [TableExportController::class, 'index'])->name('index');
                Route::post('{tableKey}', [TableExportController::class, 'store'])->name('store');
                Route::get('{export}/download', [TableExportController::class, 'download'])->name('download');
                Route::delete('{export}', [TableExportController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('bulk-jobs')->as('bulk-jobs.')->group(function (): void {
                Route::get('/', [BulkJobController::class, 'index'])
                    ->middleware('fresh')
                    ->name('index');
                Route::post('/', [BulkJobController::class, 'store'])->name('store');
            });

            Route::prefix('reports')->as('reports.')->group(function (): void {
                Route::get('/', [ReportController::class, 'index'])->name('index');
                Route::get('list', [ReportController::class, 'list'])
                    ->middleware('fresh')
                    ->name('list');
                Route::post('{reportKey}/refresh', [ReportController::class, 'refresh'])->name('refresh');
                Route::get('download/{snapshot}', [ReportController::class, 'download'])
                    ->middleware('fresh')
                    ->name('download');
            });

            Route::get('metrics/tablekit', [MetricsController::class, 'index'])
                ->middleware('can:update,' . Setting::class)
                ->name('metrics.tablekit');

            Route::get('metrics/auth-audit', [AuthAuditController::class, 'index'])
                ->middleware('can:viewAuthAuditMetrics')
                ->name('metrics.auth-audit');

            if (config('features.legacy_routing.inventory_pricelists')) {
                Route::get('inventory/pricelists', function () {
                    return redirect()->route('admin.marketing.pricelists.index');
                })->name('legacy.inventory.pricelists.index');

                Route::get('inventory/pricelists/{pricelist}', function ($pricelist) {
                    return redirect()->route('admin.marketing.pricelists.show', ['pricelist' => $pricelist]);
                })->name('legacy.inventory.pricelists.show');
            }

            if (config('features.legacy_routing.inventory_bom')) {
                Route::get('inventory/bom', function () {
                    return redirect()->route('admin.production.boms.index');
                })->name('legacy.inventory.bom.index');

                Route::get('inventory/bom/{product}', function ($product) {
                    $companyId = currentCompanyId();
                    $bom = \App\Modules\Production\Domain\Models\Bom::query()
                        ->where('company_id', $companyId)
                        ->where('product_id', $product)
                        ->orderByDesc('is_active')
                        ->orderByDesc('version')
                        ->first();

                    if ($bom) {
                        return redirect()->route('admin.production.boms.show', $bom);
                    }

                    return redirect()->route('admin.production.boms.index');
                })->name('legacy.inventory.bom.show');
            }

            $settingsRoutes = base_path('app/Modules/Settings/Routes/admin.php');
            if (file_exists($settingsRoutes)) {
                require $settingsRoutes;
            }

            Route::fallback(function () {
                abort(404);
            });
        });
    });
