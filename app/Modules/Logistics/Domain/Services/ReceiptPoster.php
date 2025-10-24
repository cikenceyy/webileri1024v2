<?php

namespace App\Modules\Logistics\Domain\Services;

use App\Modules\Inventory\Domain\Models\StockLedgerEntry;
use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LogicException;

class ReceiptPoster
{
    public function receive(GoodsReceipt $receipt, array $linePayloads, ?int $warehouseId): void
    {
        if (! in_array($receipt->status, ['draft', 'received'], true)) {
            throw new LogicException('Receipt cannot be posted in its current status.');
        }

        DB::transaction(function () use ($receipt, $linePayloads, $warehouseId) {
            $receipt->load('lines');
            $timestamp = CarbonImmutable::now();

            foreach ($receipt->lines as $line) {
                $data = Arr::get($linePayloads, $line->id, []);
                if (! $data) {
                    continue;
                }

                $line->update([
                    'qty_expected' => $data['qty_expected'] ?? $line->qty_expected,
                    'qty_received' => $data['qty_received'],
                    'warehouse_id' => $data['warehouse_id'] ?? null,
                    'bin_id' => $data['bin_id'] ?? null,
                    'variance_reason' => $data['variance_reason'] ?? $line->variance_reason,
                ]);

                $qty = (float) $line->qty_received;
                if ($qty <= 0) {
                    continue;
                }

                $targetWarehouse = $line->warehouse_id ?: ($warehouseId ?: $receipt->warehouse_id);
                if (! $targetWarehouse) {
                    throw new LogicException('Mal kabul satırı için depo seçilmelidir.');
                }

                StockLedgerEntry::create([
                    'company_id' => $receipt->company_id,
                    'product_id' => $line->product_id,
                    'warehouse_id' => $targetWarehouse,
                    'bin_id' => $line->bin_id,
                    'qty_in' => $qty,
                    'qty_out' => 0,
                    'reason' => 'grn',
                    'ref_type' => 'goods_receipt',
                    'ref_id' => $receipt->id,
                    'doc_no' => $receipt->doc_no,
                    'dated_at' => $timestamp,
                ]);
            }

            $receipt->forceFill([
                'status' => 'received',
                'warehouse_id' => $warehouseId ?: $receipt->warehouse_id,
                'received_at' => $timestamp,
            ])->save();
        });
    }
}
