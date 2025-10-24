<?php

namespace App\Consoles\Domain;

use App\Core\Contracts\SettingsReader;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use App\Modules\Logistics\Domain\Models\GoodsReceiptLine;
use App\Modules\Logistics\Domain\Services\LogisticsSequencer;
use App\Modules\Logistics\Domain\Services\ReceiptPoster;
use App\Modules\Logistics\Domain\Services\ReceiptReconciler;
use App\Modules\Procurement\Domain\Models\PoLine;
use App\Modules\Procurement\Domain\Models\PurchaseOrder;
use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use LogicException;

class P2PConsoleService
{
    public function __construct(
        private readonly LogisticsSequencer $sequencer,
        private readonly ReceiptPoster $poster,
        private readonly ReceiptReconciler $reconciler,
        private readonly SettingsReader $settings,
        private readonly ConnectionInterface $db,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(int $companyId, array $filters = []): array
    {
        $poQuery = PurchaseOrder::query()
            ->with('lines')
            ->where('company_id', $companyId)
            ->orderByDesc('created_at');

        if ($supplier = Arr::get($filters, 'supplier_id')) {
            $poQuery->where('supplier_id', $supplier);
        }

        if ($status = Arr::get($filters, 'status')) {
            $poQuery->where('status', $status);
        }

        $purchaseOrders = $poQuery->limit(25)->get();

        $receipts = GoodsReceipt::query()
            ->with('lines')
            ->where('company_id', $companyId)
            ->whereIn('status', ['draft', 'received', 'reconciled'])
            ->latest()
            ->limit(25)
            ->get();

        $warehouses = Warehouse::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return [
            'totals' => [
                'open_pos' => (int) PurchaseOrder::query()
                    ->where('company_id', $companyId)
                    ->whereNotIn('status', ['closed', 'cancelled'])
                    ->count(),
                'awaiting_receipt' => (int) GoodsReceipt::query()
                    ->where('company_id', $companyId)
                    ->whereIn('status', ['draft'])
                    ->count(),
                'received_today' => (int) GoodsReceipt::query()
                    ->where('company_id', $companyId)
                    ->whereDate('received_at', CarbonImmutable::now()->toDateString())
                    ->count(),
            ],
            'steps' => [
                [
                    'key' => 'purchase_orders',
                    'label' => 'Satın Alma Siparişleri',
                    'items' => $purchaseOrders->map(fn (PurchaseOrder $po) => $this->formatPurchaseOrder($po))->all(),
                ],
                [
                    'key' => 'receipts',
                    'label' => 'Mal Kabuller',
                    'items' => $receipts->map(fn (GoodsReceipt $receipt) => $this->formatReceipt($receipt))->all(),
                ],
            ],
            'warehouses' => $warehouses,
            'filters' => [
                'supplier_id' => Arr::get($filters, 'supplier_id'),
                'status' => Arr::get($filters, 'status'),
            ],
        ];
    }

    /**
     * @param  array<int>  $poIds
     */
    public function approvePurchaseOrders(int $companyId, array $poIds): void
    {
        if (empty($poIds)) {
            return;
        }

        PurchaseOrder::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $poIds)
            ->update([
                'status' => 'approved',
                'approved_at' => CarbonImmutable::now(),
            ]);
    }

    /**
     * @param  array<int>  $poIds
     * @return array<int>
     */
    public function createReceiptsFromPurchaseOrders(int $companyId, array $poIds, ?int $warehouseId = null): array
    {
        $orders = PurchaseOrder::query()
            ->with('lines')
            ->where('company_id', $companyId)
            ->whereIn('id', $poIds)
            ->get();

        if ($orders->isEmpty()) {
            return [];
        }

        $settings = $this->settings->get($companyId);
        $warehouseId ??= Arr::get($settings->defaults, 'receipt_warehouse_id');

        if (! $warehouseId) {
            throw new LogicException(__('Varsayılan mal kabul deposu ayarlanmadı.'));
        }

        $created = [];

        $this->db->transaction(function () use ($orders, $companyId, $warehouseId, &$created): void {
            foreach ($orders as $order) {
                $docNo = $this->sequencer->nextReceipt($companyId);

                $receipt = GoodsReceipt::create([
                    'company_id' => $companyId,
                    'doc_no' => $docNo,
                    'vendor_id' => $order->supplier_id,
                    'source_type' => PurchaseOrder::class,
                    'source_id' => $order->getKey(),
                    'status' => 'draft',
                    'warehouse_id' => $warehouseId,
                ]);

                foreach ($order->lines as $index => $line) {
                    GoodsReceiptLine::create([
                        'company_id' => $companyId,
                        'receipt_id' => $receipt->id,
                        'product_id' => $line->product_id,
                        'source_line_type' => PoLine::class,
                        'source_line_id' => $line->id,
                        'qty_expected' => $line->qty_ordered,
                        'qty_received' => 0,
                        'sort' => $index,
                    ]);
                }

                $created[] = $receipt->getKey();
            }
        });

        return $created;
    }

    /**
     * @param  array<int>  $receiptIds
     */
    public function receiveReceipts(int $companyId, array $receiptIds, ?int $warehouseId = null): void
    {
        $receipts = $this->loadReceipts($companyId, $receiptIds);
        if ($receipts->isEmpty()) {
            return;
        }

        foreach ($receipts as $receipt) {
            $payload = [];
            foreach ($receipt->lines as $line) {
                $expected = $line->qty_expected ?? $line->qty_received ?? 0;
                $payload[$line->id] = [
                    'qty_expected' => $expected,
                    'qty_received' => $expected,
                    'warehouse_id' => $line->warehouse_id ?: ($warehouseId ?: $receipt->warehouse_id),
                    'bin_id' => $line->bin_id,
                ];
            }

            $this->poster->receive($receipt, $payload, $warehouseId);
        }
    }

    /**
     * @param  array<int>  $receiptIds
     */
    public function reconcileReceipts(int $companyId, array $receiptIds, ?string $reason = null): void
    {
        $receipts = $this->loadReceipts($companyId, $receiptIds);
        if ($receipts->isEmpty()) {
            return;
        }

        foreach ($receipts as $receipt) {
            $payload = [];
            foreach ($receipt->lines as $line) {
                $payload[$line->id] = [
                    'variance_reason' => $reason ?: $line->variance_reason,
                ];
            }

            $this->reconciler->reconcile($receipt, $payload);
        }
    }

    private function formatPurchaseOrder(PurchaseOrder $order): array
    {
        $number = $order->getAttribute('po_number');

        return [
            'id' => $order->getKey(),
            'number' => $number ?: ('PO-' . str_pad((string) $order->getKey(), 5, '0', STR_PAD_LEFT)),
            'status' => $order->status,
            'total' => (float) $order->total,
            'currency' => $order->currency,
            'lines' => $order->lines->count(),
        ];
    }

    private function formatReceipt(GoodsReceipt $receipt): array
    {
        return [
            'id' => $receipt->getKey(),
            'doc_no' => $receipt->doc_no,
            'status' => $receipt->status,
            'line_count' => $receipt->lines->count(),
            'received_at' => optional($receipt->received_at)->toDateTimeString(),
        ];
    }

    /**
     * @param  array<int>  $receiptIds
     * @return Collection<int, GoodsReceipt>
     */
    private function loadReceipts(int $companyId, array $receiptIds): Collection
    {
        if (empty($receiptIds)) {
            return new Collection();
        }

        return GoodsReceipt::query()
            ->with('lines')
            ->where('company_id', $companyId)
            ->whereIn('id', $receiptIds)
            ->get();
    }
}
