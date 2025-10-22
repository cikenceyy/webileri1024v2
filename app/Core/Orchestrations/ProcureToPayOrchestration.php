<?php

namespace App\Core\Orchestrations;

use App\Core\Orchestrations\Concerns\ResolvesTenant;
use App\Core\Orchestrations\Contracts\Dto\P2PState;
use App\Core\Orchestrations\Contracts\Dto\StepResult;
use App\Core\Orchestrations\Contracts\OrchestrationContract;
use App\Modules\Finance\Domain\Models\ApInvoice;
use App\Modules\Finance\Domain\Models\ApInvoiceLine;
use App\Modules\Finance\Domain\Models\ApPayment;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Services\StockService;
use App\Modules\Procurement\Domain\Models\Grn;
use App\Modules\Procurement\Domain\Models\PurchaseOrder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProcureToPayOrchestration implements OrchestrationContract
{
    use ResolvesTenant;

    /**
     * @var array<string, string>
     */
    private const STEP_PERMISSION_MAP = [
        'po.approve' => 'procurement.po.approve',
        'grn.receive' => 'inventory.grn.receive',
        'ap.invoice.post' => 'finance.ap.post',
        'ap.payment.register' => 'finance.payment.register',
    ];

    /**
     * @var array<string, string|null>
     */
    private const NEXT_STEP_MAP = [
        'po.approve' => 'grn.receive',
        'grn.receive' => 'ap.invoice.post',
        'ap.invoice.post' => 'ap.payment.register',
        'ap.payment.register' => null,
    ];

    public function preview(array $filters): array
    {
        $companyId = $this->resolveCompanyId();

        $purchaseOrders = PurchaseOrder::query()
            ->where('company_id', $companyId);

        $goodsReceipts = Grn::query()->where('company_id', $companyId);
        $apInvoices = ApInvoice::query()->where('company_id', $companyId);

        $kpis = [
            'open_pos' => (clone $purchaseOrders)->whereNotIn('status', ['closed', 'cancelled'])->count(),
            'awaiting_receipt' => (clone $purchaseOrders)->whereIn('status', ['approved', 'partially_received'])->count(),
            'pending_grn' => (clone $goodsReceipts)->whereIn('status', ['draft', 'pending'])->count(),
            'ap_due' => (clone $apInvoices)
                ->whereNotIn('status', ['paid', 'void'])
                ->whereDate('due_date', '<=', Carbon::now()->addDays(7))
                ->count(),
        ];

        $pipeline = [
            [
                'label' => 'Onay Bekleyen PO\'lar',
                'action' => 'po.approve',
                'count' => (clone $purchaseOrders)->whereIn('status', ['draft', 'pending'])->count(),
                'rows' => $this->formatPurchaseOrders((clone $purchaseOrders)
                    ->whereIn('status', ['draft', 'pending'])
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get()),
            ],
            [
                'label' => 'Kabul Bekleyen Sevkiyatlar',
                'action' => 'grn.receive',
                'count' => (clone $goodsReceipts)->whereIn('status', ['draft', 'pending'])->count(),
                'rows' => $this->formatReceipts((clone $goodsReceipts)
                    ->whereIn('status', ['draft', 'pending'])
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get()),
            ],
            [
                'label' => 'Faturalandırılacak Tedarikler',
                'action' => 'ap.invoice.post',
                'count' => (clone $purchaseOrders)->whereIn('status', ['received', 'closed'])->count(),
                'rows' => $this->formatPurchaseOrders((clone $purchaseOrders)
                    ->whereIn('status', ['received', 'closed'])
                    ->orderByDesc('updated_at')
                    ->limit(5)
                    ->get()),
            ],
            [
                'label' => 'Ödeme Bekleyen AP Faturaları',
                'action' => 'ap.payment.register',
                'count' => (clone $apInvoices)->whereNotIn('status', ['paid', 'void'])
                    ->where(function ($q): void {
                        $q->whereNull('balance_due')->orWhere('balance_due', '>', 0);
                    })
                    ->count(),
                'rows' => $this->formatApInvoices((clone $apInvoices)
                    ->whereNotIn('status', ['paid', 'void'])
                    ->orderBy('due_date')
                    ->limit(5)
                    ->get()),
            ],
        ];

        return (new P2PState(
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
                    'po.approve' => $this->approvePurchaseOrder($payload),
                    'grn.receive' => $this->receiveGoods($payload),
                    'ap.invoice.post' => $this->postAccountsPayableInvoice($payload),
                    'ap.payment.register' => $this->registerApPayment($payload),
                    default => StepResult::failure(__('Tanımsız adım: :step', ['step' => $step])),
                };
            });
        } catch (ValidationException $e) {
            return StepResult::failure(__('İşlem doğrulamada başarısız oldu.'), $e->errors());
        } catch (\Throwable $e) {
            Log::error('ProcureToPay step failed', [
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

    private function approvePurchaseOrder(array $payload): StepResult
    {
        $companyId = $this->resolveCompanyId();
        $poId = Arr::get($payload, 'purchase_order_id');
        $po = PurchaseOrder::query()
            ->where('company_id', $companyId)
            ->findOrFail($poId);

        $po->forceFill([
            'status' => 'approved',
            'approved_at' => now(),
        ])->save();

        return StepResult::success(
            __('Satın alma siparişi onaylandı.'),
            ['purchase_order_id' => $po->getKey(), 'status' => $po->status],
            self::NEXT_STEP_MAP['po.approve']
        );
    }

    private function receiveGoods(array $payload): StepResult
    {
        $companyId = $this->resolveCompanyId();
        $grnId = Arr::get($payload, 'grn_id');
        $receipt = Grn::query()
            ->where('company_id', $companyId)
            ->with('lines.product')
            ->findOrFail($grnId);

        $receipt->forceFill([
            'status' => 'received',
            'received_at' => $receipt->received_at ?: now(),
        ])->save();

        if (class_exists(StockService::class)) {
            /** @var StockService $stock */
            $stock = app(StockService::class);
            $warehouse = Warehouse::query()
                ->where('company_id', $receipt->company_id)
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->first();

            if ($warehouse) {
                foreach ($receipt->lines as $line) {
                    $product = $line->product;
                    if (! $product) {
                        continue;
                    }

                    $qty = (float) $line->qty_received;
                    if ($qty <= 0) {
                        continue;
                    }

                    $stock->receive($warehouse, $product, null, $qty, null, [
                        'reason' => 'procurement',
                        'ref_type' => Grn::class,
                        'ref_id' => $receipt->getKey(),
                    ]);
                }
            }
        }

        return StepResult::success(
            __('Mal kabul kaydedildi.'),
            ['grn_id' => $receipt->getKey(), 'status' => $receipt->status],
            self::NEXT_STEP_MAP['grn.receive']
        );
    }

    private function postAccountsPayableInvoice(array $payload): StepResult
    {
        $companyId = $this->resolveCompanyId();
        $poId = Arr::get($payload, 'purchase_order_id');
        $po = PurchaseOrder::query()
            ->where('company_id', $companyId)
            ->with('lines')
            ->findOrFail($poId);

        $expectedTotal = $po->lines->sum(fn ($line) => (float) $line->line_total);

        $invoice = ApInvoice::create([
            'company_id' => $po->company_id,
            'purchase_order_id' => $po->getKey(),
            'supplier_id' => $po->supplier_id,
            'status' => 'posted',
            'invoice_date' => Arr::get($payload, 'invoice_date', now()->toDateString()),
            'due_date' => Arr::get($payload, 'due_date', now()->addDays(30)->toDateString()),
            'currency' => Arr::get($payload, 'currency', 'TRY'),
            'expected_total' => $expectedTotal,
            'notes' => Arr::get($payload, 'notes'),
        ]);

        foreach ($po->lines as $line) {
            ApInvoiceLine::create([
                'company_id' => $invoice->company_id,
                'ap_invoice_id' => $invoice->getKey(),
                'description' => $line->description,
                'qty' => $line->qty_ordered,
                'unit' => $line->unit,
                'unit_price' => $line->unit_price,
                'amount' => $line->line_total,
                'source_type' => get_class($line),
                'source_uuid' => $line->uuid,
            ]);
        }

        $invoice->refreshTotals();
        $invoice->save();

        return StepResult::success(
            __('Satın alma faturası oluşturuldu.'),
            ['ap_invoice_id' => $invoice->getKey(), 'status' => $invoice->status],
            self::NEXT_STEP_MAP['ap.invoice.post']
        );
    }

    private function registerApPayment(array $payload): StepResult
    {
        $companyId = $this->resolveCompanyId();
        $invoiceId = Arr::get($payload, 'ap_invoice_id');
        $invoice = ApInvoice::query()
            ->where('company_id', $companyId)
            ->findOrFail($invoiceId);

        $amount = (float) Arr::get($payload, 'amount', 0);
        if ($amount <= 0) {
            $amount = (float) $invoice->balance_due;
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => __('Ödenecek tutar bulunamadı.'),
            ]);
        }

        $payment = ApPayment::create([
            'company_id' => $invoice->company_id,
            'ap_invoice_id' => $invoice->getKey(),
            'paid_at' => Arr::get($payload, 'paid_at', now()->toDateString()),
            'amount' => $amount,
            'method' => Arr::get($payload, 'method', 'bank-transfer'),
            'reference' => Arr::get($payload, 'reference'),
            'notes' => Arr::get($payload, 'notes'),
        ]);

        $invoice->refreshTotals();
        $invoice->status = $invoice->balance_due > 0 ? 'partial' : 'paid';
        $invoice->save();

        return StepResult::success(
            __('Ödeme kaydedildi.'),
            ['ap_invoice_id' => $invoice->getKey(), 'status' => $invoice->status, 'payment_id' => $payment->getKey()],
            self::NEXT_STEP_MAP['ap.payment.register']
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function formatPurchaseOrders($purchaseOrders): array
    {
        return $purchaseOrders->map(static function (PurchaseOrder $po): array {
            return [
                'id' => $po->getKey(),
                'number' => $po->po_number,
                'reference' => $po->po_number,
                'status' => $po->status,
                'total' => (float) $po->total,
                'supplier_id' => $po->supplier_id,
                'updated_at' => optional($po->updated_at)->toDateString(),
            ];
        })->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function formatReceipts($receipts): array
    {
        return $receipts->map(static function (Grn $grn): array {
            return [
                'id' => $grn->getKey(),
                'status' => $grn->status,
                'received_at' => optional($grn->received_at)->toDateTimeString(),
                'purchase_order_id' => $grn->purchase_order_id,
            ];
        })->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function formatApInvoices($invoices): array
    {
        return $invoices->map(static function (ApInvoice $invoice): array {
            return [
                'id' => $invoice->getKey(),
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
            'supplier_id' => Arr::get($filters, 'supplier_id'),
            'search' => Arr::get($filters, 'search'),
            'from' => Arr::get($filters, 'from'),
            'to' => Arr::get($filters, 'to'),
        ];
    }
}
