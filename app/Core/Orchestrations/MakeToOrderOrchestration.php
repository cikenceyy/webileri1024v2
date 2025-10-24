<?php

namespace App\Core\Orchestrations;

use App\Core\Orchestrations\Concerns\ResolvesTenant;
use App\Core\Orchestrations\Contracts\Dto\MTOState;
use App\Core\Orchestrations\Contracts\Dto\StepResult;
use App\Core\Orchestrations\Contracts\OrchestrationContract;
use App\Core\Contracts\SettingsReader;
use App\Modules\Marketing\Domain\Models\Order as LegacyOrder;
use App\Modules\Marketing\Domain\Models\OrderLine as LegacyOrderLine;
use App\Modules\Marketing\Domain\Models\SalesOrder;
use App\Modules\Marketing\Domain\Models\SalesOrderLine;
use App\Modules\Production\Domain\Models\WorkOrder;
use App\Modules\Production\Domain\Services\BomExpander;
use App\Modules\Production\Domain\Services\WorkOrderCompleter;
use App\Modules\Production\Domain\Services\WorkOrderIssuer;
use App\Modules\Production\Domain\Services\WorkOrderPlanner;
use App\Modules\Settings\Domain\SettingsDTO;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MakeToOrderOrchestration implements OrchestrationContract
{
    use ResolvesTenant;

    /**
     * @var array<string, string>
     */
    private const STEP_PERMISSION_MAP = [
        'wo.release' => 'production.workorders.release',
        'wo.issue.materials' => 'production.workorders.issue',
        'wo.finish' => 'production.workorders.complete',
        'wo.close' => 'production.workorders.close',
    ];

    /**
     * @var array<string, string|null>
     */
    private const NEXT_STEP_MAP = [
        'wo.release' => 'wo.issue.materials',
        'wo.issue.materials' => 'wo.finish',
        'wo.finish' => 'wo.close',
        'wo.close' => null,
    ];

    public function preview(array $filters): array
    {
        $companyId = $this->resolveCompanyId();

        $workOrders = WorkOrder::query()
            ->where('company_id', $companyId);

        $kpis = [
            'draft' => (clone $workOrders)->where('status', 'draft')->count(),
            'in_progress' => (clone $workOrders)->whereIn('status', ['released', 'in_progress'])->count(),
            'completed' => (clone $workOrders)->where('status', 'completed')->count(),
            'closed' => (clone $workOrders)->where('status', 'closed')->count(),
        ];

        $pipeline = [
            [
                'label' => 'Serbest Bırakılacak İş Emirleri',
                'action' => 'wo.release',
                'count' => (clone $workOrders)->where('status', 'draft')->count(),
                'rows' => $this->formatWorkOrders((clone $workOrders)
                    ->where('status', 'draft')
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get()),
            ],
            [
                'label' => 'Malzeme İhtiyacı Olanlar',
                'action' => 'wo.issue.materials',
                'count' => (clone $workOrders)->where('status', 'released')->count(),
                'rows' => $this->formatWorkOrders((clone $workOrders)
                    ->where('status', 'released')
                    ->orderBy('due_date')
                    ->limit(5)
                    ->get()),
            ],
            [
                'label' => 'Tamamlanması Gerekenler',
                'action' => 'wo.finish',
                'count' => (clone $workOrders)->where('status', 'in_progress')->count(),
                'rows' => $this->formatWorkOrders((clone $workOrders)
                    ->where('status', 'in_progress')
                    ->orderBy('due_date')
                    ->limit(5)
                    ->get()),
            ],
            [
                'label' => 'Kapatılacak İş Emirleri',
                'action' => 'wo.close',
                'count' => (clone $workOrders)->where('status', 'completed')->count(),
                'rows' => $this->formatWorkOrders((clone $workOrders)
                    ->where('status', 'completed')
                    ->orderByDesc('updated_at')
                    ->limit(5)
                    ->get()),
            ],
        ];

        return (new MTOState(
            kpis: $kpis,
            pipeline: $pipeline,
            filters: [
                'status' => Arr::get($filters, 'status'),
                'product_id' => Arr::get($filters, 'product_id'),
            ],
        ))->toArray();
    }

    public function executeStep(string $step, array $payload, ?string $idempotencyKey = null): StepResult
    {
        $permission = self::STEP_PERMISSION_MAP[$step] ?? null;
        $user = Auth::user();

        if ($permission && $user && ! $user->can($permission)) {
            return StepResult::failure(__('Bu adımı yürütme yetkiniz bulunmuyor.'));
        }

        try {
            $result = DB::transaction(function () use ($step, $payload) {
                return match ($step) {
                    'wo.release' => $this->releaseWorkOrders($payload),
                    'wo.issue.materials' => $this->issueMaterials($payload),
                    'wo.finish' => $this->finishWorkOrder($payload),
                    'wo.close' => $this->closeWorkOrder($payload),
                    default => StepResult::failure(__('Tanımsız adım: :step', ['step' => $step])),
                };
            });
        } catch (ValidationException $e) {
            return StepResult::failure(__('İşlem doğrulamada başarısız oldu.'), $e->errors());
        } catch (\Throwable $e) {
            Log::error('MakeToOrder step failed', [
                'step' => $step,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            return StepResult::failure(__('İşlem sırasında bir hata oluştu.'), ['exception' => $e->getMessage()]);
        }

        $next = self::NEXT_STEP_MAP[$step] ?? null;

        return StepResult::success($result->message, $result->data, $next);
    }

    public function rollbackStep(string $step, array $payload): StepResult
    {
        return StepResult::failure(__('Bu adım için geri alma henüz uygulanmadı.'));
    }

    private function releaseWorkOrders(array $payload): StepResult
    {
        $companyId = $this->resolveCompanyId();
        $orderId = Arr::get($payload, 'order_id');
        $created = [];
        $released = [];

        if ($orderId) {
            $order = SalesOrder::query()
                ->where('company_id', $companyId)
                ->with('lines')
                ->find($orderId);

            if (! $order) {
                $order = LegacyOrder::query()
                    ->where('company_id', $companyId)
                    ->with('lines')
                    ->find($orderId);
            }

            if ($order) {
                foreach ($order->lines as $line) {
                    $workOrder = $this->createDraftWorkOrder($line);
                    if ($workOrder) {
                        $created[] = $workOrder->getKey();

                        if (in_array($workOrder->status, ['draft', 'cancelled'], true)) {
                            $workOrder->forceFill([
                                'status' => 'released',
                            ])->save();

                            $released[] = $workOrder->getKey();
                        }
                    }
                }
            }
        }

        $workOrderId = Arr::get($payload, 'work_order_id');
        if ($workOrderId) {
            $workOrder = WorkOrder::query()
                ->where('company_id', $companyId)
                ->findOrFail($workOrderId);

            if (! in_array($workOrder->status, ['draft', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'status' => __('Yalnızca taslak iş emirleri serbest bırakılabilir.'),
                ]);
            }

            $workOrder->forceFill([
                'status' => 'released',
            ])->save();

            $released[] = $workOrder->getKey();
        }

        return StepResult::success(
            __('İş emri serbest bırakıldı.'),
            [
                'order_id' => $orderId,
                'created_work_orders' => $created,
                'work_order_id' => $workOrderId,
                'released_work_orders' => array_values(array_unique($released)),
            ],
            self::NEXT_STEP_MAP['wo.release']
        );
    }

    private function issueMaterials(array $payload): StepResult
    {
        $companyId = $this->resolveCompanyId();
        $workOrderId = Arr::get($payload, 'work_order_id');

        $workOrder = WorkOrder::query()
            ->where('company_id', $companyId)
            ->with('bom.items')
            ->findOrFail($workOrderId);

        if (! in_array($workOrder->status, ['released', 'in_progress'], true)) {
            throw ValidationException::withMessages([
                'status' => __('İş emri malzeme çıkışı için uygun değil.'),
            ]);
        }

        $settings = $this->resolveSettings($companyId);
        $defaults = $settings->defaults;
        $issueWarehouseId = Arr::get($payload, 'warehouse_id')
            ?? Arr::get($defaults, 'production_issue_warehouse_id');
        $precision = (int) Arr::get($settings->general, 'decimal_precision', 3);

        $materials = Arr::get($payload, 'materials', []);

        if (empty($materials)) {
            $requirements = app(BomExpander::class)->expand($workOrder->bom, $workOrder->target_qty, $precision);
            $materials = $requirements->map(static function (array $row) use ($issueWarehouseId) {
                $item = $row['item'];

                return [
                    'component_product_id' => $item->component_product_id,
                    'component_variant_id' => $item->component_variant_id,
                    'warehouse_id' => $item->default_warehouse_id ?: $issueWarehouseId,
                    'bin_id' => $item->default_bin_id,
                    'qty' => $row['required_qty'],
                ];
            })->all();
        }

        $materials = array_values(array_filter(array_map(static function (array $line) use ($issueWarehouseId) {
            $line['warehouse_id'] = $line['warehouse_id'] ?? $issueWarehouseId;

            if (($line['qty'] ?? 0) <= 0) {
                return null;
            }

            if (! ($line['component_product_id'] ?? null) || ! ($line['warehouse_id'] ?? null)) {
                return null;
            }

            return [
                'component_product_id' => (int) $line['component_product_id'],
                'component_variant_id' => $line['component_variant_id'] ? (int) $line['component_variant_id'] : null,
                'warehouse_id' => (int) $line['warehouse_id'],
                'bin_id' => $line['bin_id'] ? (int) $line['bin_id'] : null,
                'qty' => (float) $line['qty'],
            ];
        }, $materials)));

        if (empty($materials)) {
            throw ValidationException::withMessages([
                'materials' => __('Çıkış yapılacak malzeme bulunamadı.'),
            ]);
        }

        /** @var WorkOrderIssuer $issuer */
        $issuer = app(WorkOrderIssuer::class);
        $workOrder = $issuer->post($workOrder, $materials, Auth::id() ?? 0);

        return StepResult::success(
            __('Malzemeler iş emrine aktarıldı.'),
            [
                'work_order_id' => $workOrder->getKey(),
                'issues' => $workOrder->issues->pluck('id')->all(),
            ],
            self::NEXT_STEP_MAP['wo.issue.materials']
        );
    }

    private function finishWorkOrder(array $payload): StepResult
    {
        $companyId = $this->resolveCompanyId();
        $workOrderId = Arr::get($payload, 'work_order_id');

        $workOrder = WorkOrder::query()
            ->where('company_id', $companyId)
            ->findOrFail($workOrderId);

        $settings = $this->resolveSettings($companyId);
        $defaults = $settings->defaults;

        $qty = (float) ($payload['qty'] ?? $workOrder->target_qty);
        if ($qty <= 0) {
            throw ValidationException::withMessages([
                'qty' => __('Tamamlanan miktar sıfırdan büyük olmalıdır.'),
            ]);
        }

        $warehouseId = Arr::get($payload, 'warehouse_id')
            ?? Arr::get($defaults, 'production_receipt_warehouse_id');

        if (! $warehouseId) {
            throw ValidationException::withMessages([
                'warehouse_id' => __('Ürünlerin alınacağı depo seçilmelidir.'),
            ]);
        }

        $data = [
            'qty' => $qty,
            'warehouse_id' => (int) $warehouseId,
            'bin_id' => Arr::get($payload, 'bin_id') ? (int) Arr::get($payload, 'bin_id') : null,
        ];

        /** @var WorkOrderCompleter $completer */
        $completer = app(WorkOrderCompleter::class);
        $workOrder = $completer->post($workOrder, $data, Auth::id() ?? 0);

        return StepResult::success(
            __('İş emri tamamlandı.'),
            [
                'work_order_id' => $workOrder->getKey(),
                'receipt_ids' => $workOrder->receipts->pluck('id')->all(),
            ],
            self::NEXT_STEP_MAP['wo.finish']
        );
    }

    private function closeWorkOrder(array $payload): StepResult
    {
        $companyId = $this->resolveCompanyId();
        $workOrderId = Arr::get($payload, 'work_order_id');

        $workOrder = WorkOrder::query()
            ->where('company_id', $companyId)
            ->findOrFail($workOrderId);

        if ($workOrder->status !== 'completed') {
            throw ValidationException::withMessages([
                'status' => __('Kapatma için iş emri tamamlanmış olmalıdır.'),
            ]);
        }

        $workOrder->forceFill([
            'status' => 'closed',
        ])->save();

        return StepResult::success(
            __('İş emri kapatıldı.'),
            [
                'work_order_id' => $workOrder->getKey(),
                'status' => $workOrder->status,
            ],
            self::NEXT_STEP_MAP['wo.close']
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function formatWorkOrders($workOrders): array
    {
        return $workOrders->map(static function (WorkOrder $workOrder): array {
            return [
                'id' => $workOrder->getKey(),
                'doc_no' => $workOrder->doc_no,
                'status' => $workOrder->status,
                'qty' => (float) $workOrder->target_qty,
                'product_id' => $workOrder->product_id,
                'due_date' => optional($workOrder->due_date)->toDateString(),
            ];
        })->all();
    }

    private function createDraftWorkOrder(SalesOrderLine|LegacyOrderLine $line): ?WorkOrder
    {
        /** @var WorkOrderPlanner $planner */
        $planner = app(WorkOrderPlanner::class);

        return $planner->createFromOrderLine($line);
    }

    private function resolveSettings(int $companyId): SettingsDTO
    {
        /** @var SettingsReader $reader */
        $reader = app(SettingsReader::class);

        return $reader->get($companyId);
    }
}
