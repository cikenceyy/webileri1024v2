<?php

namespace App\Core\Orchestrations;

use App\Core\Orchestrations\Concerns\ResolvesTenant;
use App\Core\Orchestrations\Contracts\Dto\O2CState;
use App\Core\Orchestrations\Contracts\Dto\StepResult;
use App\Core\Orchestrations\Contracts\OrchestrationContract;
use App\Modules\Finance\Domain\Models\Allocation;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use App\Modules\Finance\Domain\Services\BillingService;
use App\Modules\Inventory\Domain\Services\StockService;
use App\Modules\Logistics\Domain\Models\Shipment;
use App\Modules\Logistics\Domain\Services\ShipmentService;
use App\Modules\Marketing\Domain\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderToCashOrchestration implements OrchestrationContract
{
    use ResolvesTenant;

    /**
     * @var array<string, string>
     */
    private const STEP_PERMISSION_MAP = [
        'so.confirm' => 'marketing.orders.confirm',
        'inv.allocate' => 'inventory.stock.allocate',
        'ship.dispatch' => 'logistics.shipment.dispatch',
        'ar.invoice.post' => 'finance.invoice.post',
        'ar.payment.register' => 'finance.payment.register',
    ];

    /**
     * @var array<string, string|null>
     */
    private const NEXT_STEP_MAP = [
        'so.confirm' => 'inv.allocate',
        'inv.allocate' => 'ship.dispatch',
        'ship.dispatch' => 'ar.invoice.post',
        'ar.invoice.post' => 'ar.payment.register',
        'ar.payment.register' => null,
    ];

    public function preview(array $filters): array
    {
        $companyId = $this->resolveCompanyId();

        $orders = Order::query()
            ->where('company_id', $companyId)
            ->with('customer');
        $orders = $this->applyOrderFilters($orders, $filters);

        $shipments = Shipment::query()
            ->where('company_id', $companyId);

        $invoices = Invoice::query()
            ->where('company_id', $companyId);

        $kpis = [
            'open_orders' => (clone $orders)->whereNotIn('status', ['fulfilled', 'cancelled', 'closed'])->count(),
            'awaiting_fulfilment' => (clone $orders)->whereIn('status', ['confirmed', 'ready_to_ship'])->count(),
            'shipments_in_progress' => (clone $shipments)->whereNotIn('status', ['delivered', 'returned'])->count(),
            'invoices_due' => (clone $invoices)
                ->whereIn('status', ['draft', 'sent', 'partial'])
                ->whereDate('due_date', '<=', Carbon::now()->addDays(7))
                ->count(),
        ];

        $pipeline = [
            [
                'label' => 'Onay Bekleyen Siparişler',
                'action' => 'so.confirm',
                'count' => (clone $orders)->whereIn('status', ['draft', 'pending', 'awaiting_approval'])->count(),
                'rows' => $this->formatOrders((clone $orders)
                    ->whereIn('status', ['draft', 'pending', 'awaiting_approval'])
                    ->orderByDesc('order_date')
                    ->limit(5)
                    ->get()),
            ],
            [
                'label' => 'Sevk Edilmeyi Bekleyenler',
                'action' => 'ship.dispatch',
                'count' => (clone $shipments)->whereIn('status', ['draft', 'picking', 'packed'])->count(),
                'rows' => $this->formatShipments((clone $shipments)
                    ->whereIn('status', ['draft', 'picking', 'packed'])
                    ->orderByDesc('ship_date')
                    ->limit(5)
                    ->get()),
            ],
            [
                'label' => 'Faturası Kesilecek Siparişler',
                'action' => 'ar.invoice.post',
                'count' => (clone $orders)->whereIn('status', ['ready_to_invoice', 'shipped'])->count(),
                'rows' => $this->formatOrders((clone $orders)
                    ->whereIn('status', ['ready_to_invoice', 'shipped'])
                    ->orderByDesc('order_date')
                    ->limit(5)
                    ->get()),
            ],
            [
                'label' => 'Tahsilat Bekleyen Faturalar',
                'action' => 'ar.payment.register',
                'count' => (clone $invoices)->whereNotIn('status', ['paid', 'void'])
                    ->where(function ($q): void {
                        $q->whereNull('balance_due')->orWhere('balance_due', '>', 0);
                    })->count(),
                'rows' => $this->formatInvoices((clone $invoices)
                    ->whereNotIn('status', ['paid', 'void'])
                    ->orderBy('due_date')
                    ->limit(5)
                    ->get()),
            ],
        ];

        return (new O2CState(
            kpis: $kpis,
            pipeline: $pipeline,
            filters: $this->normalizeFilters($filters),
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
            $result = DB::transaction(function () use ($step, $payload, $idempotencyKey) {
                return match ($step) {
                    'so.confirm' => $this->confirmOrder($payload, $idempotencyKey),
                    'inv.allocate' => $this->allocateInventory($payload, $idempotencyKey),
                    'ship.dispatch' => $this->dispatchShipment($payload, $idempotencyKey),
                    'ar.invoice.post' => $this->postInvoice($payload, $idempotencyKey),
                    'ar.payment.register' => $this->registerReceipt($payload, $idempotencyKey),
                    default => StepResult::failure(__('Tanımsız adım: :step', ['step' => $step])),
                };
            });
        } catch (ValidationException $e) {
            return StepResult::failure(__('İşlem doğrulamada başarısız oldu.'), $e->errors());
        } catch (\Throwable $e) {
            Log::error('OrderToCash step failed', [
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

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Order>  $orders
     */
    private function applyOrderFilters($orders, array $filters)
    {
        if ($status = Arr::get($filters, 'status')) {
            $orders->where('status', $status);
        }

        if ($customerId = Arr::get($filters, 'customer_id')) {
            $orders->where('customer_id', $customerId);
        }

        if ($term = Arr::get($filters, 'search')) {
            $orders->search($term);
        }

        if ($from = Arr::get($filters, 'from')) {
            $orders->whereDate('order_date', '>=', $from);
        }

        if ($to = Arr::get($filters, 'to')) {
            $orders->whereDate('order_date', '<=', $to);
        }

        return $orders;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function formatOrders($orders): array
    {
        return $orders->map(function (Order $order): array {
            return [
                'id' => $order->getKey(),
                'order_no' => $order->order_no,
                'customer' => optional($order->customer)->name,
                'status' => $order->status,
                'total' => (float) $order->total_amount,
                'order_date' => optional($order->order_date)->toDateString(),
            ];
        })->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function formatShipments($shipments): array
    {
        return $shipments->map(static function (Shipment $shipment): array {
            return [
                'id' => $shipment->getKey(),
                'shipment_no' => $shipment->shipment_no,
                'status' => $shipment->status,
                'ship_date' => optional($shipment->ship_date)->toDateString(),
                'order_id' => $shipment->order_id,
            ];
        })->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function formatInvoices($invoices): array
    {
        return $invoices->map(static function (Invoice $invoice): array {
            return [
                'id' => $invoice->getKey(),
                'invoice_no' => $invoice->invoice_no,
                'status' => $invoice->status,
                'due_date' => optional($invoice->due_date)->toDateString(),
                'balance_due' => (float) $invoice->balance_due,
            ];
        })->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeFilters(array $filters): array
    {
        return [
            'status' => Arr::get($filters, 'status'),
            'customer_id' => Arr::get($filters, 'customer_id'),
            'search' => Arr::get($filters, 'search'),
            'from' => Arr::get($filters, 'from'),
            'to' => Arr::get($filters, 'to'),
        ];
    }

    private function confirmOrder(array $payload, ?string $idempotencyKey = null): StepResult
    {
        $companyId = $this->resolveCompanyId();
        $orderId = Arr::get($payload, 'order_id');
        $order = Order::query()
            ->where('company_id', $companyId)
            ->findOrFail($orderId);

        $order->forceFill([
            'status' => 'confirmed',
        ])->save();

        if (class_exists(StockService::class)) {
            /** @var StockService $service */
            $service = app(StockService::class);
            $service->reserveForOrder($order->fresh('lines.product', 'lines.variant'));
        }

        return StepResult::success(
            __('Sipariş onaylandı.'),
            ['order_id' => $order->getKey(), 'status' => $order->status],
            self::NEXT_STEP_MAP['so.confirm']
        );
    }

    private function allocateInventory(array $payload, ?string $idempotencyKey = null): StepResult
    {
        if (! class_exists(StockService::class)) {
            return StepResult::success(__('Stok servisi bulunamadı, işlem atlandı.'));
        }

        $companyId = $this->resolveCompanyId();
        $orderId = Arr::get($payload, 'order_id');
        $order = Order::query()
            ->where('company_id', $companyId)
            ->findOrFail($orderId);

        /** @var StockService $service */
        $service = app(StockService::class);
        $service->reserveForOrder($order->fresh('lines.product', 'lines.variant'));

        return StepResult::success(
            __('Stok rezervasyonu tamamlandı.'),
            ['order_id' => $order->getKey()],
            self::NEXT_STEP_MAP['inv.allocate']
        );
    }

    private function dispatchShipment(array $payload, ?string $idempotencyKey = null): StepResult
    {
        $companyId = $this->resolveCompanyId();
        $shipmentId = Arr::get($payload, 'shipment_id');
        $shipment = Shipment::query()
            ->where('company_id', $companyId)
            ->findOrFail($shipmentId);

        if (class_exists(ShipmentService::class)) {
            /** @var ShipmentService $service */
            $service = app(ShipmentService::class);
            $shipment = $service->ship($shipment);
        } else {
            $shipment->forceFill([
                'status' => 'shipped',
                'shipped_at' => now(),
            ])->save();
        }

        return StepResult::success(
            __('Sevkiyat sevk edildi.'),
            ['shipment_id' => $shipment->getKey(), 'status' => $shipment->status],
            self::NEXT_STEP_MAP['ship.dispatch']
        );
    }

    private function postInvoice(array $payload, ?string $idempotencyKey = null): StepResult
    {
        $companyId = $this->resolveCompanyId();
        $orderId = Arr::get($payload, 'order_id');
        $order = Order::query()
            ->where('company_id', $companyId)
            ->findOrFail($orderId);

        $invoice = null;

        if (class_exists(BillingService::class)) {
            /** @var BillingService $service */
            $service = app(BillingService::class);
            $invoice = $service->fromOrder($order->fresh('lines'));
        } else {
            $invoice = Invoice::create([
                'company_id' => $order->company_id,
                'customer_id' => $order->customer_id,
                'order_id' => $order->getKey(),
                'invoice_no' => Invoice::generateInvoiceNo($order->company_id),
                'issue_date' => now()->toDateString(),
                'due_date' => $order->due_date ?? now()->addDays(14)->toDateString(),
                'currency' => $order->currency,
                'status' => 'sent',
                'subtotal' => $order->subtotal,
                'tax_total' => $order->tax_total,
                'grand_total' => $order->total_amount,
                'balance_due' => $order->total_amount,
            ]);
        }

        $invoice->refreshTotals();
        $invoice->status = $invoice->balance_due > 0 ? 'sent' : 'paid';
        $invoice->save();

        return StepResult::success(
            __('Fatura oluşturuldu.'),
            ['invoice_id' => $invoice->getKey(), 'status' => $invoice->status],
            self::NEXT_STEP_MAP['ar.invoice.post']
        );
    }

    private function registerReceipt(array $payload, ?string $idempotencyKey = null): StepResult
    {
        $companyId = $this->resolveCompanyId();
        $invoiceId = Arr::get($payload, 'invoice_id');
        $amount = (float) Arr::get($payload, 'amount', 0);

        $invoice = Invoice::query()
            ->where('company_id', $companyId)
            ->findOrFail($invoiceId);

        if ($amount <= 0) {
            $amount = (float) $invoice->balance_due;
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => __('Tahsil edilecek tutar bulunamadı.'),
            ]);
        }

        $receipt = Receipt::create([
            'company_id' => $invoice->company_id,
            'customer_id' => $invoice->customer_id,
            'receipt_no' => 'RC-' . now()->format('Ymd-His'),
            'receipt_date' => Arr::get($payload, 'receipt_date', now()->toDateString()),
            'currency' => $invoice->currency,
            'amount' => $amount,
            'allocated_total' => 0,
            'notes' => Arr::get($payload, 'notes'),
            'bank_account_id' => Arr::get($payload, 'bank_account_id'),
            'created_by' => optional(Auth::user())->getKey(),
        ]);

        Allocation::create([
            'company_id' => $invoice->company_id,
            'invoice_id' => $invoice->getKey(),
            'receipt_id' => $receipt->getKey(),
            'amount' => $amount,
            'allocated_at' => now(),
            'allocated_by' => optional(Auth::user())->getKey(),
        ]);

        $receipt->refreshAllocatedTotal();
        $invoice->refreshTotals();
        $invoice->status = $invoice->balance_due > 0 ? 'partial' : 'paid';
        $invoice->save();

        return StepResult::success(
            __('Tahsilat kaydedildi.'),
            [
                'invoice_id' => $invoice->getKey(),
                'receipt_id' => $receipt->getKey(),
                'status' => $invoice->status,
            ],
            self::NEXT_STEP_MAP['ar.payment.register']
        );
    }
}
