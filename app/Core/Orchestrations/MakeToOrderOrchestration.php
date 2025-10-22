<?php

namespace App\Core\Orchestrations;

use App\Core\Orchestrations\Concerns\ResolvesTenant;
use App\Core\Orchestrations\Contracts\Dto\MTOState;
use App\Core\Orchestrations\Contracts\Dto\StepResult;
use App\Core\Orchestrations\Contracts\OrchestrationContract;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Services\StockService;
use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Marketing\Domain\Models\OrderLine;
use App\Modules\Production\Domain\Models\WoMaterialIssue;
use App\Modules\Production\Domain\Models\WoReceipt;
use App\Modules\Production\Domain\Models\WorkOrder;
use App\Modules\Production\Domain\Services\WoService;
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
        'wo.release' => 'production.wo.release',
        'wo.issue.materials' => 'inventory.issue',
        'wo.finish' => 'production.wo.finish',
        'inv.receive.finished' => 'inventory.receive',
    ];

    /**
     * @var array<string, string|null>
     */
    private const NEXT_STEP_MAP = [
        'wo.release' => 'wo.issue.materials',
        'wo.issue.materials' => 'wo.finish',
        'wo.finish' => 'inv.receive.finished',
        'inv.receive.finished' => null,
    ];

    public function preview(array $filters): array
    {
        $companyId = $this->resolveCompanyId();

        $workOrders = WorkOrder::query()
            ->where('company_id', $companyId);

        $kpis = [
            'draft' => (clone $workOrders)->where('status', 'draft')->count(),
            'in_progress' => (clone $workOrders)->whereIn('status', ['released', 'in_progress'])->count(),
            'awaiting_qc' => (clone $workOrders)->where('status', 'awaiting_qc')->count(),
            'completed' => (clone $workOrders)->where('status', 'done')->count(),
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
                'count' => (clone $workOrders)->whereIn('status', ['released'])->count(),
                'rows' => $this->formatWorkOrders((clone $workOrders)
                    ->whereIn('status', ['released'])
                    ->orderBy('due_date')
                    ->limit(5)
                    ->get()),
            ],
            [
                'label' => 'Tamamlanmaya Yakın İşler',
                'action' => 'wo.finish',
                'count' => (clone $workOrders)->whereIn('status', ['in_progress'])->count(),
                'rows' => $this->formatWorkOrders((clone $workOrders)
                    ->whereIn('status', ['in_progress'])
                    ->orderBy('due_date')
                    ->limit(5)
                    ->get()),
            ],
            [
                'label' => 'Depoya Alınacak Ürünler',
                'action' => 'inv.receive.finished',
                'count' => (clone $workOrders)->whereIn('status', ['finished', 'done'])->count(),
                'rows' => $this->formatWorkOrders((clone $workOrders)
                    ->whereIn('status', ['finished', 'done'])
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
                    'inv.receive.finished' => $this->receiveFinishedGoods($payload),
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
        $order = Order::query()
            ->where('company_id', $companyId)
            ->with('lines')
            ->find($orderId);

        if ($order) {
            if (class_exists(WoService::class)) {
                /** @var WoService $service */
                $service = app(WoService::class);
                $service->proposeFromOrder($order);
            } else {
                foreach ($order->lines as $line) {
                    $this->createDraftWorkOrder($line);
                }
            }
        }

        $workOrderId = Arr::get($payload, 'work_order_id');
        if ($workOrderId) {
            $workOrder = WorkOrder::query()
                ->where('company_id', $companyId)
                ->findOrFail($workOrderId);

            $workOrder->forceFill([
                'status' => 'released',
            ])->save();
        }

        return StepResult::success(
            __('İş emri serbest bırakıldı.'),
            ['order_id' => $order?->getKey(), 'work_order_id' => $workOrderId],
            self::NEXT_STEP_MAP['wo.release']
        );
    }

    private function issueMaterials(array $payload): StepResult
    {
        $workOrderId = Arr::get($payload, 'work_order_id');
        $workOrder = WorkOrder::query()
            ->where('company_id', $this->resolveCompanyId())
            ->findOrFail($workOrderId);

        $materials = Arr::get($payload, 'materials', []);
        $issued = [];

        if (class_exists(StockService::class)) {
            /** @var StockService $stock */
            $stock = app(StockService::class);
            $warehouse = Warehouse::query()
                ->where('company_id', $workOrder->company_id)
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->first();

            foreach ($materials as $material) {
                $product = Product::query()
                    ->where('company_id', $workOrder->company_id)
                    ->find($material['product_id'] ?? null);

                if (! $product) {
                    continue;
                }

                $variant = null;
                if (! empty($material['variant_id'])) {
                    $variant = ProductVariant::query()
                        ->where('company_id', $workOrder->company_id)
                        ->find($material['variant_id']);
                }

                $qty = (float) ($material['qty'] ?? 0);
                if ($qty <= 0 || ! $warehouse) {
                    continue;
                }

                $stock->issue($warehouse, $product, $variant, $qty, [
                    'reason' => 'production',
                    'ref_type' => WorkOrder::class,
                    'ref_id' => $workOrder->getKey(),
                ]);

                $issue = WoMaterialIssue::create([
                    'company_id' => $workOrder->company_id,
                    'work_order_id' => $workOrder->getKey(),
                    'product_id' => $product->getKey(),
                    'variant_id' => $variant?->getKey(),
                    'qty' => $qty,
                    'unit' => $material['unit'] ?? $product->unit ?? 'adet',
                    'issued_at' => now(),
                    'notes' => $material['notes'] ?? null,
                ]);

                $issued[] = $issue->getKey();
            }
        }

        $workOrder->forceFill([
            'status' => 'in_progress',
        ])->save();

        return StepResult::success(
            __('Malzemeler iş emrine aktarıldı.'),
            ['work_order_id' => $workOrder->getKey(), 'issues' => $issued],
            self::NEXT_STEP_MAP['wo.issue.materials']
        );
    }

    private function finishWorkOrder(array $payload): StepResult
    {
        $workOrderId = Arr::get($payload, 'work_order_id');
        $workOrder = WorkOrder::query()
            ->where('company_id', $this->resolveCompanyId())
            ->findOrFail($workOrderId);

        if (class_exists(WoService::class)) {
            /** @var WoService $service */
            $service = app(WoService::class);
            $service->close($workOrder);
        } else {
            $workOrder->forceFill([
                'status' => 'done',
                'closed_at' => now(),
            ])->save();
        }

        return StepResult::success(
            __('İş emri tamamlandı.'),
            ['work_order_id' => $workOrder->getKey(), 'status' => $workOrder->status],
            self::NEXT_STEP_MAP['wo.finish']
        );
    }

    private function receiveFinishedGoods(array $payload): StepResult
    {
        $workOrderId = Arr::get($payload, 'work_order_id');
        $workOrder = WorkOrder::query()
            ->where('company_id', $this->resolveCompanyId())
            ->findOrFail($workOrderId);

        $product = Product::query()
            ->where('company_id', $workOrder->company_id)
            ->find($workOrder->product_id);

        if (! $product) {
            throw ValidationException::withMessages([
                'product_id' => __('İş emrine bağlı ürün bulunamadı.'),
            ]);
        }

        $variant = null;
        if ($workOrder->variant_id) {
            $variant = ProductVariant::query()
                ->where('company_id', $workOrder->company_id)
                ->find($workOrder->variant_id);
        }

        $qty = (float) Arr::get($payload, 'qty', $workOrder->qty);
        if ($qty <= 0) {
            throw ValidationException::withMessages([
                'qty' => __('Depoya alınacak miktar sıfırdan büyük olmalıdır.'),
            ]);
        }

        $warehouse = Warehouse::query()
            ->where('company_id', $workOrder->company_id)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();

        if ($warehouse && class_exists(StockService::class)) {
            /** @var StockService $stock */
            $stock = app(StockService::class);
            $stock->receive($warehouse, $product, $variant, $qty, null, [
                'reason' => 'production-finish',
                'ref_type' => WorkOrder::class,
                'ref_id' => $workOrder->getKey(),
            ]);
        }

        $receipt = WoReceipt::create([
            'company_id' => $workOrder->company_id,
            'work_order_id' => $workOrder->getKey(),
            'product_id' => $product->getKey(),
            'variant_id' => $variant?->getKey(),
            'qty' => $qty,
            'unit' => $workOrder->unit,
            'received_at' => now(),
            'notes' => Arr::get($payload, 'notes'),
        ]);

        $workOrder->forceFill([
            'status' => 'done',
            'closed_at' => $workOrder->closed_at ?: now(),
        ])->save();

        return StepResult::success(
            __('Ürün depoya alındı.'),
            ['work_order_id' => $workOrder->getKey(), 'receipt_id' => $receipt->getKey()],
            self::NEXT_STEP_MAP['inv.receive.finished']
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
                'work_order_no' => $workOrder->work_order_no,
                'status' => $workOrder->status,
                'qty' => (float) $workOrder->qty,
                'product_id' => $workOrder->product_id,
                'due_date' => optional($workOrder->due_date)->toDateString(),
            ];
        })->all();
    }

    private function createDraftWorkOrder(OrderLine $line): void
    {
        WorkOrder::firstOrCreate(
            [
                'company_id' => $line->company_id,
                'order_line_id' => $line->getKey(),
            ],
            [
                'order_id' => $line->order_id,
                'product_id' => $line->product_id,
                'variant_id' => $line->variant_id,
                'work_order_no' => WorkOrder::generateNo($line->company_id),
                'qty' => $line->qty,
                'unit' => $line->unit ?? 'adet',
                'status' => 'draft',
                'due_date' => optional($line->order)->due_date,
            ]
        );
    }
}
