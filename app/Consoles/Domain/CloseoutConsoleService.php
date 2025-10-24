<?php

namespace App\Consoles\Domain;

use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use App\Modules\Logistics\Domain\Models\Shipment;
use Carbon\CarbonImmutable;

class CloseoutConsoleService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(int $companyId, ?string $date = null): array
    {
        $date = $date ? CarbonImmutable::parse($date) : CarbonImmutable::now();
        $from = $date->startOfDay();
        $to = $date->endOfDay();

        $shipments = Shipment::query()
            ->where('company_id', $companyId)
            ->where('status', 'shipped')
            ->whereBetween('shipped_at', [$from, $to])
            ->orderByDesc('shipped_at')
            ->get();

        $invoices = Invoice::query()
            ->where('company_id', $companyId)
            ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PAID])
            ->whereBetween('issued_at', [$from, $to])
            ->orderByDesc('issued_at')
            ->get();

        $receipts = Receipt::query()
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get();

        $goodsReceipts = GoodsReceipt::query()
            ->where('company_id', $companyId)
            ->whereBetween('received_at', [$from, $to])
            ->orderByDesc('received_at')
            ->get();

        return [
            'date' => $date->toDateString(),
            'shipments' => $shipments,
            'invoices' => $invoices,
            'receipts' => $receipts,
            'goods_receipts' => $goodsReceipts,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $selection
     * @return array<int, array<string, string>>
     */
    public function batchPrint(array $selection): array
    {
        $links = [];
        foreach ($selection as $item) {
            $type = $item['type'] ?? null;
            $id = $item['id'] ?? null;
            if (! $type || ! $id) {
                continue;
            }

            $links[] = match ($type) {
                'shipment' => [
                    'label' => __('Sevkiyat #:id', ['id' => $id]),
                    'url' => route('admin.logistics.shipments.print', $id),
                ],
                'invoice' => [
                    'label' => __('Fatura #:id', ['id' => $id]),
                    'url' => route('admin.finance.invoices.print', $id),
                ],
                'goods_receipt' => [
                    'label' => __('GRN #:id', ['id' => $id]),
                    'url' => route('admin.logistics.receipts.print', $id),
                ],
                default => [
                    'label' => __('Kayıt #:id', ['id' => $id]),
                    'url' => route('admin.finance.receipts.show', $id),
                ],
            };
        }

        return $links;
    }
}
