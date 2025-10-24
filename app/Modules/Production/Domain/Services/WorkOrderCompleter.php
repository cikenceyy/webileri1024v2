<?php

namespace App\Modules\Production\Domain\Services;

use App\Modules\Inventory\Domain\Models\StockLedgerEntry;
use App\Modules\Production\Domain\Models\WorkOrder;
use App\Modules\Production\Domain\Models\WorkOrderReceipt;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WorkOrderCompleter
{
    /**
     * @param array<string, mixed> $payload
     */
    public function post(WorkOrder $workOrder, array $payload, int $userId): WorkOrder
    {
        if (! in_array($workOrder->status, ['released', 'in_progress'], true)) {
            throw new InvalidArgumentException('Work order must be in progress before completion.');
        }

        $companyId = (int) $workOrder->company_id;
        $qty = (float) Arr::get($payload, 'qty', 0);
        if ($qty <= 0) {
            throw new InvalidArgumentException('Completion quantity must be positive.');
        }

        return DB::transaction(function () use ($workOrder, $payload, $qty, $userId, $companyId) {
            $postedAt = now();

            $receipt = WorkOrderReceipt::create([
                'company_id' => $companyId,
                'work_order_id' => $workOrder->id,
                'warehouse_id' => Arr::get($payload, 'warehouse_id'),
                'bin_id' => Arr::get($payload, 'bin_id') ?: null,
                'qty' => $qty,
                'posted_at' => $postedAt,
                'posted_by' => $userId,
            ]);

            StockLedgerEntry::create([
                'company_id' => $companyId,
                'product_id' => $workOrder->product_id,
                'warehouse_id' => $receipt->warehouse_id,
                'bin_id' => $receipt->bin_id,
                'qty_in' => $qty,
                'qty_out' => 0,
                'reason' => 'wo_receipt',
                'ref_type' => 'work_order',
                'ref_id' => $workOrder->id,
                'doc_no' => $workOrder->doc_no,
                'dated_at' => $postedAt,
            ]);

            $workOrder->forceFill([
                'status' => 'completed',
                'completed_at' => $postedAt,
            ])->save();

            return $workOrder->fresh(['issues', 'receipts']);
        });
    }
}
