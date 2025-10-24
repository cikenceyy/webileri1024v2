<?php

namespace App\Consoles\Domain;

use App\Core\Contracts\SettingsReader;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use App\Modules\Finance\Domain\Services\InvoiceCalculator;
use App\Modules\Finance\Domain\Services\NumberSequencer;
use App\Modules\Finance\Domain\Services\ReceiptAllocator;
use App\Modules\Logistics\Domain\Models\Shipment;
use App\Modules\Logistics\Domain\Models\ShipmentLine;
use App\Modules\Logistics\Domain\Services\LogisticsSequencer;
use App\Modules\Logistics\Domain\Services\ShipmentPacker;
use App\Modules\Logistics\Domain\Services\ShipmentPicker;
use App\Modules\Logistics\Domain\Services\ShipmentShipper;
use App\Modules\Marketing\Domain\Models\SalesOrder;
use App\Modules\Marketing\Domain\Models\SalesOrderLine;
use App\Modules\Marketing\Domain\StockSignal;
use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use LogicException;

class O2CConsoleService
{
    public function __construct(
        private readonly SettingsReader $settings,
        private readonly LogisticsSequencer $sequencer,
        private readonly ShipmentPicker $picker,
        private readonly ShipmentPacker $packer,
        private readonly ShipmentShipper $shipper,
        private readonly InvoiceCalculator $calculator,
        private readonly NumberSequencer $numberSequencer,
        private readonly ReceiptAllocator $receiptAllocator,
        private readonly StockSignal $stockSignal,
        private readonly ConnectionInterface $db,
        private readonly QualityConsoleService $quality,
    ) {
    }

    /**
     * @return array{
     *     totals: array<string, int|float>,
     *     steps: array<int, array<string, mixed>>,
     *     filters: array<string, mixed>
     * }
     */
    public function summary(int $companyId, array $filters = []): array
    {
        $ordersQuery = SalesOrder::query()
            ->with(['customer:id,name', 'lines'])
            ->where('company_id', $companyId)
            ->whereIn('status', [SalesOrder::STATUS_CONFIRMED, SalesOrder::STATUS_FULFILLED])
            ->orderByDesc('ordered_at');

        $ordersQuery = $this->applyOrderFilters($ordersQuery, $filters);

        $orders = $ordersQuery
            ->limit(25)
            ->get();

        $shipments = Shipment::query()
            ->with(['customer:id,name', 'lines'])
            ->where('company_id', $companyId)
            ->whereIn('status', ['draft', 'picking', 'packed'])
            ->latest()
            ->limit(25)
            ->get();

        $invoices = Invoice::query()
            ->with('customer:id,name')
            ->where('company_id', $companyId)
            ->whereIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIALLY_PAID])
            ->orderBy('due_date')
            ->limit(25)
            ->get();

        $receipts = Receipt::query()
            ->with('customer:id,name')
            ->where('company_id', $companyId)
            ->whereDate('received_at', CarbonImmutable::now()->toDateString())
            ->latest()
            ->limit(25)
            ->get();

        return [
            'totals' => [
                'orders' => (int) SalesOrder::query()
                    ->where('company_id', $companyId)
                    ->where('status', SalesOrder::STATUS_CONFIRMED)
                    ->count(),
                'shipments' => (int) Shipment::query()
                    ->where('company_id', $companyId)
                    ->whereIn('status', ['draft', 'picking', 'packed'])
                    ->count(),
                'invoices_due' => (int) Invoice::query()
                    ->where('company_id', $companyId)
                    ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIALLY_PAID])
                    ->count(),
                'receipts_today' => (int) $receipts->count(),
            ],
            'steps' => [
                [
                    'key' => 'orders',
                    'label' => 'Siparişler',
                    'items' => $orders->map(fn (SalesOrder $order) => $this->formatOrder($order))->all(),
                ],
                [
                    'key' => 'shipments',
                    'label' => 'Sevkiyatlar',
                    'items' => $shipments->map(fn (Shipment $shipment) => $this->formatShipment($shipment))->all(),
                ],
                [
                    'key' => 'invoices',
                    'label' => 'Faturalar',
                    'items' => $invoices->map(fn (Invoice $invoice) => $this->formatInvoice($invoice))->all(),
                ],
                [
                    'key' => 'receipts',
                    'label' => 'Tahsilatlar',
                    'items' => $receipts->map(fn (Receipt $receipt) => $this->formatReceipt($receipt))->all(),
                ],
            ],
            'filters' => [
                'status' => Arr::get($filters, 'status'),
                'customer_id' => Arr::get($filters, 'customer_id'),
                'from' => Arr::get($filters, 'from'),
                'to' => Arr::get($filters, 'to'),
            ],
        ];
    }

    /**
     * @param  array<int>  $orderIds
     * @return array<int>
     */
    public function createShipments(int $companyId, array $orderIds, array $context = []): array
    {
        if (empty($orderIds)) {
            throw new InvalidArgumentException('Sevkiyat oluşturmak için en az bir sipariş seçin.');
        }

        $settings = $this->settings->get($companyId);
        $warehouseId = $context['warehouse_id'] ?? Arr::get($settings->defaults, 'shipment_warehouse_id');

        if (! $warehouseId) {
            throw new LogicException(__('Varsayılan sevkiyat deposu ayarlanmadı.')); 
        }

        $orders = SalesOrder::query()
            ->with(['lines', 'customer'])
            ->where('company_id', $companyId)
            ->whereIn('id', $orderIds)
            ->get();

        if ($orders->isEmpty()) {
            throw new LogicException(__('Seçilen sipariş bulunamadı.'));
        }

        $created = [];

        $this->db->transaction(function () use ($orders, $warehouseId, &$created, $companyId): void {
            foreach ($orders as $order) {
                if (! $order->isConfirmed()) {
                    continue;
                }

                $docNo = $this->sequencer->nextShipment($companyId);
                $shipment = Shipment::create([
                    'company_id' => $companyId,
                    'doc_no' => $docNo,
                    'customer_id' => $order->customer_id,
                    'source_type' => SalesOrder::class,
                    'source_id' => $order->getKey(),
                    'status' => 'draft',
                    'warehouse_id' => $warehouseId,
                ]);

                foreach ($order->lines as $index => $line) {
                    ShipmentLine::create([
                        'company_id' => $companyId,
                        'shipment_id' => $shipment->id,
                        'product_id' => $line->product_id,
                        'variant_id' => $line->variant_id,
                        'source_line_type' => SalesOrderLine::class,
                        'source_line_id' => $line->id,
                        'qty' => $line->qty,
                        'uom' => $line->uom,
                        'picked_qty' => 0,
                        'packed_qty' => 0,
                        'warehouse_id' => $warehouseId,
                        'sort' => $index,
                    ]);
                }

                $shipment->status = 'picking';
                $shipment->save();

                $created[] = $shipment->getKey();
            }
        });

        return $created;
    }

    /**
     * @param  array<int>  $shipmentIds
     */
    public function pickShipments(int $companyId, array $shipmentIds): void
    {
        $shipments = $this->loadShipments($companyId, $shipmentIds);
        if ($shipments->isEmpty()) {
            return;
        }

        foreach ($shipments as $shipment) {
            $payload = [];
            foreach ($shipment->lines as $line) {
                $payload[$line->id] = [
                    'picked_qty' => $line->qty,
                    'warehouse_id' => $line->warehouse_id ?: $shipment->warehouse_id,
                    'bin_id' => $line->bin_id,
                ];
            }

            $this->picker->pick($shipment, $payload);
        }
    }

    /**
     * @param  array<int>  $shipmentIds
     */
    public function packShipments(int $companyId, array $shipmentIds, ?int $packagesCount = null): void
    {
        $shipments = $this->loadShipments($companyId, $shipmentIds);
        if ($shipments->isEmpty()) {
            return;
        }

        foreach ($shipments as $shipment) {
            $payload = [];
            foreach ($shipment->lines as $line) {
                $packedQty = $line->packed_qty ?: ($line->picked_qty ?: $line->qty);
                $payload[$line->id] = [
                    'packed_qty' => $packedQty,
                ];
            }

            $this->packer->pack($shipment, $payload, $packagesCount, null, null);
        }
    }

    /**
     * @param  array<int>  $shipmentIds
     */
    public function shipShipments(int $companyId, array $shipmentIds): void
    {
        $shipments = $this->loadShipments($companyId, $shipmentIds);
        if ($shipments->isEmpty()) {
            return;
        }

        foreach ($shipments as $shipment) {
            if (config('features.logistics.quality_blocking') && $this->quality->hasBlockingFailure($companyId, Shipment::class, $shipment->getKey())) {
                throw new LogicException(__('Kalite kontrol başarısız olan sevkiyat sevk edilemez.'));
            }

            if ($shipment->status !== 'packed') {
                // Auto-pack remaining quantities before shipping.
                $payload = [];
                foreach ($shipment->lines as $line) {
                    $qty = $line->packed_qty ?: ($line->picked_qty ?: $line->qty);
                    $payload[$line->id] = ['packed_qty' => $qty];
                }

                $this->packer->pack($shipment, $payload, $shipment->packages_count, $shipment->gross_weight, $shipment->net_weight);
                $shipment->refresh();
            }

            $this->shipper->ship($shipment);
        }
    }

    /**
     * @param  array<int>  $orderIds
     * @return array<int>
     */
    public function createInvoices(int $companyId, array $orderIds): array
    {
        if (empty($orderIds)) {
            return [];
        }

        $settings = $this->settings->get($companyId);

        $orders = SalesOrder::query()
            ->with(['lines', 'customer'])
            ->where('company_id', $companyId)
            ->whereIn('id', $orderIds)
            ->get();

        $invoiceIds = [];

        $this->db->transaction(function () use ($orders, $settings, &$invoiceIds, $companyId): void {
            foreach ($orders as $order) {
                $lines = $order->lines->map(function (SalesOrderLine $line): array {
                    return [
                        'product_id' => $line->product_id,
                        'variant_id' => $line->variant_id,
                        'description' => $line->product?->name ?? __('Satır'),
                        'qty' => $line->qty,
                        'uom' => $line->uom,
                        'unit_price' => $line->unit_price,
                        'discount_pct' => $line->discount_pct,
                        'tax_rate' => $line->tax_rate,
                    ];
                })->toArray();

                $calculation = $this->calculator->calculate($lines, (bool) $order->tax_inclusive);
                $terms = $order->payment_terms_days ?: Arr::get($settings->defaults, 'payment_terms_days', 0);

                $invoice = Invoice::create([
                    'company_id' => $companyId,
                    'customer_id' => $order->customer_id,
                    'order_id' => $order->getKey(),
                    'currency' => $order->currency ?: Arr::get($settings->money, 'base_currency', 'TRY'),
                    'tax_inclusive' => (bool) $order->tax_inclusive,
                    'payment_terms_days' => $terms,
                    'subtotal' => $calculation['totals']['subtotal'],
                    'tax_total' => $calculation['totals']['tax'],
                    'grand_total' => $calculation['totals']['grand'],
                    'status' => Invoice::STATUS_DRAFT,
                ]);

                foreach ($calculation['lines'] as $line) {
                    $invoice->lines()->create(array_merge($line, [
                        'company_id' => $companyId,
                        'sort' => $line['sort'] ?? 0,
                    ]));
                }

                $docNo = $this->numberSequencer->nextInvoiceNumber($companyId);
                $invoice->markIssued($docNo, CarbonImmutable::now(), $terms);

                $invoiceIds[] = $invoice->getKey();
            }
        });

        return $invoiceIds;
    }

    /**
     * @param  array<int>  $invoiceIds
     */
    public function applyReceipts(int $companyId, array $invoiceIds, ?CarbonImmutable $receivedAt = null): void
    {
        $invoices = Invoice::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $invoiceIds)
            ->get();

        if ($invoices->isEmpty()) {
            return;
        }

        $receivedAt = $receivedAt ?: CarbonImmutable::now();

        $this->db->transaction(function () use ($invoices, $companyId, $receivedAt): void {
            foreach ($invoices as $invoice) {
                $balance = max(0.0, (float) $invoice->grand_total - (float) $invoice->paid_amount);
                if ($balance <= 0) {
                    continue;
                }

                $receipt = Receipt::create([
                    'company_id' => $companyId,
                    'customer_id' => $invoice->customer_id,
                    'doc_no' => $this->numberSequencer->nextReceiptNumber($companyId),
                    'received_at' => $receivedAt->toDateString(),
                    'amount' => $balance,
                ]);

                $this->receiptAllocator->apply($receipt, [
                    ['invoice_id' => $invoice->getKey(), 'amount' => $balance],
                ]);
            }
        });
    }

    private function applyOrderFilters(Builder $query, array $filters): Builder
    {
        if ($status = Arr::get($filters, 'status')) {
            $query->where('status', $status);
        }

        if ($customerId = Arr::get($filters, 'customer_id')) {
            $query->where('customer_id', $customerId);
        }

        if ($from = Arr::get($filters, 'from')) {
            $query->whereDate('ordered_at', '>=', $from);
        }

        if ($to = Arr::get($filters, 'to')) {
            $query->whereDate('ordered_at', '<=', $to);
        }

        return $query;
    }

    private function formatOrder(SalesOrder $order): array
    {
        $companyId = (int) $order->company_id;
        $signals = $order->lines->map(function (SalesOrderLine $line) use ($companyId) {
            if (! $line->product_id) {
                return null;
            }

            return $this->stockSignal->forProduct($companyId, $line->product_id);
        })->filter()->pluck('status')->unique()->values()->all();

        $signal = empty($signals) ? 'unknown' : (in_array('out', $signals, true) ? 'out' : (in_array('low', $signals, true) ? 'low' : 'in'));

        return [
            'id' => $order->getKey(),
            'doc_no' => $order->doc_no,
            'customer' => optional($order->customer)->name,
            'total' => (float) $order->lines->sum('line_total'),
            'status' => $order->status,
            'ordered_at' => optional($order->ordered_at)->toDateString(),
            'signal' => $signal,
            'line_count' => $order->lines->count(),
        ];
    }

    private function formatShipment(Shipment $shipment): array
    {
        return [
            'id' => $shipment->getKey(),
            'doc_no' => $shipment->doc_no,
            'customer' => optional($shipment->customer)->name,
            'status' => $shipment->status,
            'lines' => $shipment->lines->count(),
            'warehouse_id' => $shipment->warehouse_id,
            'created_at' => optional($shipment->created_at)->toDateTimeString(),
        ];
    }

    private function formatInvoice(Invoice $invoice): array
    {
        $balance = max(0.0, (float) $invoice->grand_total - (float) $invoice->paid_amount);

        return [
            'id' => $invoice->getKey(),
            'doc_no' => $invoice->doc_no,
            'customer' => optional($invoice->customer)->name,
            'status' => $invoice->status,
            'grand_total' => (float) $invoice->grand_total,
            'balance' => $balance,
            'due_date' => optional($invoice->due_date)->toDateString(),
        ];
    }

    private function formatReceipt(Receipt $receipt): array
    {
        return [
            'id' => $receipt->getKey(),
            'doc_no' => $receipt->doc_no,
            'customer' => optional($receipt->customer)->name,
            'amount' => (float) $receipt->amount,
            'received_at' => optional($receipt->received_at)->toDateString(),
        ];
    }

    /**
     * @param  array<int>  $shipmentIds
     * @return Collection<int, Shipment>
     */
    private function loadShipments(int $companyId, array $shipmentIds): Collection
    {
        if (empty($shipmentIds)) {
            return new Collection();
        }

        return Shipment::query()
            ->with('lines')
            ->where('company_id', $companyId)
            ->whereIn('id', $shipmentIds)
            ->get();
    }
}
