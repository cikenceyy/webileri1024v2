<?php

namespace App\Consoles\Domain;

use App\Core\Contracts\SettingsReader;
use App\Modules\Marketing\Domain\Models\SalesOrder;
use App\Modules\Marketing\Domain\Models\SalesOrderLine;
use App\Modules\Marketing\Domain\StockSignal;
use App\Modules\Production\Domain\Models\WorkOrder;
use App\Modules\Production\Domain\Services\BomExpander;
use App\Modules\Production\Domain\Services\WorkOrderCompleter;
use App\Modules\Production\Domain\Services\WorkOrderIssuer;
use App\Modules\Production\Domain\Services\WorkOrderPlanner;
use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use LogicException;

class MTOConsoleService
{
    public function __construct(
        private readonly WorkOrderPlanner $planner,
        private readonly WorkOrderIssuer $issuer,
        private readonly WorkOrderCompleter $completer,
        private readonly BomExpander $expander,
        private readonly StockSignal $stockSignal,
        private readonly SettingsReader $settings,
        private readonly ConnectionInterface $db,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(int $companyId): array
    {
        $candidates = SalesOrderLine::query()
            ->with(['order.customer'])
            ->where('company_id', $companyId)
            ->whereHas('order', fn ($query) => $query->where('status', SalesOrder::STATUS_CONFIRMED))
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        $workOrders = WorkOrder::query()
            ->where('company_id', $companyId)
            ->with(['product', 'issues', 'receipts'])
            ->latest()
            ->limit(30)
            ->get();

        return [
            'steps' => [
                [
                    'key' => 'candidates',
                    'label' => 'MTO Adayları',
                    'items' => $candidates->map(fn (SalesOrderLine $line) => $this->formatCandidate($line))->all(),
                ],
                [
                    'key' => 'work_orders',
                    'label' => 'İş Emirleri',
                    'items' => $workOrders->map(fn (WorkOrder $wo) => $this->formatWorkOrder($wo))->all(),
                ],
            ],
            'totals' => [
                'draft' => (int) WorkOrder::query()->where('company_id', $companyId)->where('status', 'draft')->count(),
                'released' => (int) WorkOrder::query()->where('company_id', $companyId)->whereIn('status', ['released', 'in_progress'])->count(),
                'completed' => (int) WorkOrder::query()->where('company_id', $companyId)->where('status', 'completed')->count(),
            ],
        ];
    }

    /**
     * @param  array<int>  $lineIds
     * @return array<int>
     */
    public function planFromSalesLines(int $companyId, array $lineIds): array
    {
        $lines = SalesOrderLine::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $lineIds)
            ->get();

        $created = [];

        foreach ($lines as $line) {
            $workOrder = $this->planner->createFromOrderLine($line);
            if ($workOrder) {
                $workOrder->forceFill(['status' => 'released'])->save();
                $created[] = $workOrder->getKey();
            }
        }

        return $created;
    }

    /**
     * @param  array<int>  $workOrderIds
     */
    public function issueAllMaterials(int $companyId, array $workOrderIds): void
    {
        $workOrders = $this->loadWorkOrders($companyId, $workOrderIds);
        $userId = Auth::id() ?: 0;

        foreach ($workOrders as $workOrder) {
            $workOrder->loadMissing('bom.items');
            $bom = $workOrder->bom;
            if (! $bom) {
                continue;
            }

            $items = $this->expander->expand($bom, $workOrder->target_qty);
            $lines = [];
            foreach ($items as $item) {
                $component = $item['item'];
                $lines[] = [
                    'component_product_id' => $component->component_product_id,
                    'component_variant_id' => $component->component_variant_id,
                    'warehouse_id' => $component->default_warehouse_id ?? Arr::get($this->settings->get($companyId)->defaults, 'production_issue_warehouse_id'),
                    'bin_id' => $component->default_bin_id,
                    'qty' => $item['required_qty'],
                ];
            }

            $this->issuer->post($workOrder, $lines, $userId);
        }
    }

    /**
     * @param  array<int>  $workOrderIds
     */
    public function completeOrders(int $companyId, array $workOrderIds, ?float $qty = null): void
    {
        $workOrders = $this->loadWorkOrders($companyId, $workOrderIds);
        $userId = Auth::id() ?: 0;
        $settings = $this->settings->get($companyId);
        $warehouseId = Arr::get($settings->defaults, 'production_receipt_warehouse_id');

        foreach ($workOrders as $workOrder) {
            $payload = [
                'qty' => $qty ?: $workOrder->target_qty,
                'warehouse_id' => $warehouseId,
            ];

            $this->completer->post($workOrder, $payload, $userId);
        }
    }

    /**
     * @param  array<int>  $workOrderIds
     */
    public function closeOrders(int $companyId, array $workOrderIds): void
    {
        WorkOrder::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $workOrderIds)
            ->whereIn('status', ['completed'])
            ->update(['status' => 'closed']);
    }

    private function formatCandidate(SalesOrderLine $line): array
    {
        $signal = $line->product_id
            ? $this->stockSignal->forProduct((int) $line->company_id, $line->product_id)
            : ['status' => 'unknown'];

        return [
            'id' => $line->getKey(),
            'order_no' => optional($line->order)->doc_no,
            'customer' => optional($line->order?->customer)->name,
            'product' => $line->product?->name,
            'qty' => (float) $line->qty,
            'signal' => $signal['status'] ?? 'unknown',
        ];
    }

    private function formatWorkOrder(WorkOrder $workOrder): array
    {
        return [
            'id' => $workOrder->getKey(),
            'doc_no' => $workOrder->doc_no,
            'product_id' => $workOrder->product_id,
            'status' => $workOrder->status,
            'target_qty' => (float) $workOrder->target_qty,
            'issues' => $workOrder->issues?->count() ?? 0,
            'receipts' => $workOrder->receipts?->count() ?? 0,
        ];
    }

    /**
     * @param  array<int>  $workOrderIds
     * @return Collection<int, WorkOrder>
     */
    private function loadWorkOrders(int $companyId, array $workOrderIds): Collection
    {
        if (empty($workOrderIds)) {
            return new Collection();
        }

        return WorkOrder::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $workOrderIds)
            ->get();
    }
}
