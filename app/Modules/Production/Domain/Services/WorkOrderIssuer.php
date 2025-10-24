<?php

namespace App\Modules\Production\Domain\Services;

use App\Modules\Inventory\Domain\Models\StockLedgerEntry;
use App\Modules\Production\Domain\Models\WorkOrder;
use App\Modules\Production\Domain\Models\WorkOrderIssue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WorkOrderIssuer
{
    /**
     * @param array<int, array<string, mixed>> $lines
     */
    public function post(WorkOrder $workOrder, array $lines, int $userId): WorkOrder
    {
        if (! in_array($workOrder->status, ['released', 'in_progress'], true)) {
            throw new InvalidArgumentException('Work order must be released before issuing materials.');
        }

        $companyId = (int) $workOrder->company_id;

        return DB::transaction(function () use ($workOrder, $lines, $userId, $companyId) {
            $postedAt = now();

            foreach ($lines as $line) {
                $qty = (float) Arr::get($line, 'qty', 0);
                if ($qty <= 0) {
                    continue;
                }

                $issue = WorkOrderIssue::create([
                    'company_id' => $companyId,
                    'work_order_id' => $workOrder->id,
                    'component_product_id' => Arr::get($line, 'component_product_id'),
                    'component_variant_id' => Arr::get($line, 'component_variant_id') ?: null,
                    'warehouse_id' => Arr::get($line, 'warehouse_id'),
                    'bin_id' => Arr::get($line, 'bin_id') ?: null,
                    'qty' => $qty,
                    'posted_at' => $postedAt,
                    'posted_by' => $userId,
                ]);

                StockLedgerEntry::create([
                    'company_id' => $companyId,
                    'product_id' => $issue->component_product_id,
                    'warehouse_id' => $issue->warehouse_id,
                    'bin_id' => $issue->bin_id,
                    'qty_in' => 0,
                    'qty_out' => $qty,
                    'reason' => 'wo_issue',
                    'ref_type' => 'work_order',
                    'ref_id' => $workOrder->id,
                    'doc_no' => $workOrder->doc_no,
                    'dated_at' => $postedAt,
                ]);
            }

            $workOrder->forceFill([
                'status' => 'in_progress',
                'started_at' => $workOrder->started_at ?? $postedAt,
            ])->save();

            return $workOrder->fresh(['issues', 'receipts']);
        });
    }
}
