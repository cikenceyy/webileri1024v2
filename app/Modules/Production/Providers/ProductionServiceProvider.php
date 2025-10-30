<?php

namespace App\Modules\Production\Providers;

use App\Core\TableKit\QueryAdapter;
use App\Core\TableKit\TableExporterRegistry;
use App\Modules\Production\Domain\Models\Bom;
use App\Modules\Production\Domain\Models\WorkOrder;
use App\Modules\Production\Policies\BomPolicy;
use App\Modules\Production\Policies\WorkOrderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Production modülü servis sağlayıcısı: politikalar, rotalar ve TableKit export kayıtları burada tanımlanır.
 */
class ProductionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/permissions.php', 'production-permissions');
    }

    public function boot(): void
    {
        $basePath = __DIR__ . '/..';

        $this->loadRoutesFrom($basePath . '/Routes/admin.php');
        $this->loadViewsFrom($basePath . '/Resources/views', 'production');
        $this->loadMigrationsFrom($basePath . '/Database/migrations');

        Gate::policy(WorkOrder::class, WorkOrderPolicy::class);
        Gate::policy(Bom::class, BomPolicy::class);

        $this->app->afterResolving(TableExporterRegistry::class, function (TableExporterRegistry $registry): void {
            $registry->register('production:workorders', function () {
                $companyId = currentCompanyId();

                $builder = WorkOrder::query()
                    ->where('company_id', $companyId)
                    ->with(['product:id,company_id,name']);

                $configure = static function (QueryAdapter $adapter): QueryAdapter {
                    return $adapter
                        ->select([
                            'id',
                            'company_id',
                            'product_id',
                            'number',
                            'planned_qty',
                            'status',
                            'due_date',
                            'created_at',
                        ])
                        ->allowSorts(['number', 'planned_qty', 'status', 'due_date', 'created_at'])
                        ->allowFilters([
                            'status' => ['type' => 'string'],
                            'due_date' => ['type' => 'date'],
                        ])
                        ->defaultSort('-created_at');
                };

                $map = static function (WorkOrder $order): array {
                    $number = $order->number ?: sprintf('WO-%05d', $order->id);

                    return [
                        'number' => $number,
                        'product' => $order->product->name ?? '—',
                        'planned_qty' => number_format((float) ($order->planned_qty ?? 0), 0, ',', '.'),
                        'status' => $order->status ?? 'draft',
                        'due_date' => optional($order->due_date)?->format('Y-m-d') ?? '—',
                    ];
                };

                return [
                    'builder' => $builder,
                    'configure' => $configure,
                    'columns' => [
                        'number' => __('İş Emri #'),
                        'product' => __('Ürün'),
                        'planned_qty' => __('Planlanan'),
                        'status' => __('Durum'),
                        'due_date' => __('Termin'),
                    ],
                    'map' => $map,
                ];
            });
        });
    }
}
