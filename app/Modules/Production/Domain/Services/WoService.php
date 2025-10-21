<?php

namespace App\Modules\Production\Domain\Services;

use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Marketing\Domain\Models\OrderLine;
use App\Modules\Production\Domain\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WoService
{
    public function proposeFromOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $order->loadMissing(['lines.product', 'lines.variant']);

            foreach ($order->lines as $line) {
                $this->proposeFromOrderLine($line);
            }
        });
    }

    public function proposeFromOrderLine(OrderLine $line): void
    {
        DB::transaction(function () use ($line): void {
            $order = $line->order ?: Order::query()->find($line->order_id);

            if (! $order) {
                return;
            }

            $companyId = (int) $order->company_id;

            $existing = WorkOrder::query()
                ->where('company_id', $companyId)
                ->where('order_line_id', $line->id)
                ->first();

            if ($existing) {
                return;
            }

            $qty = (float) $line->qty;

            if ($qty <= 0) {
                return;
            }

            WorkOrder::create([
                'company_id' => $companyId,
                'order_id' => $order->id,
                'order_line_id' => $line->id,
                'product_id' => $line->product_id,
                'variant_id' => $line->variant_id,
                'work_order_no' => WorkOrder::generateNo($companyId),
                'qty' => $qty,
                'unit' => $line->unit ?: 'adet',
                'status' => 'draft',
                'due_date' => $order->due_date,
                'notes' => Str::limit((string) $line->description, 255),
            ]);
        });
    }

    public function close(WorkOrder $workOrder): WorkOrder
    {
        $workOrder->forceFill([
            'status' => 'done',
            'closed_at' => now(),
        ])->save();

        return $workOrder->fresh();
    }
}
