<?php

namespace App\Modules\Logistics\Domain\Services;

use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LogicException;

class ReceiptReconciler
{
    public function reconcile(GoodsReceipt $receipt, array $linePayloads): void
    {
        if ($receipt->status !== 'received') {
            throw new LogicException('Receipt must be in received status to reconcile.');
        }

        DB::transaction(function () use ($receipt, $linePayloads) {
            $receipt->load('lines');

            foreach ($receipt->lines as $line) {
                $data = Arr::get($linePayloads, $line->id, []);
                if (! $data) {
                    continue;
                }

                $line->update([
                    'variance_reason' => $data['variance_reason'] ?? $line->variance_reason,
                    'notes' => $data['notes'] ?? $line->notes,
                ]);
            }

            $receipt->forceFill(['status' => 'reconciled'])->save();
        });
    }
}
