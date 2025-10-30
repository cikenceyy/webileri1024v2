<?php

namespace App\Core\Cache\Warmers;

use App\Core\Cache\InvalidationService;
use App\Core\Cache\Keys;
use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use App\Modules\Logistics\Domain\Models\Shipment;
use Carbon\CarbonImmutable;

/**
 * Gösterge panelinde kullanılan özet verileri önceden hazırlar.
 */
class DashboardSummaryWarmer
{
    public function __construct(private readonly InvalidationService $cache)
    {
    }

    public function warm(int $companyId): void
    {
        $key = Keys::forTenant($companyId, ['dashboard', 'summary'], 'v1');
        $ttl = (int) config('cache.ttl_profiles.warm', 900);

        $this->cache->rememberWithTags(
            $key,
            [sprintf('tenant:%d', $companyId), 'dashboard'],
            $ttl,
            fn () => $this->buildPayload($companyId),
        );
    }

    private function buildPayload(int $companyId): array
    {
        $now = CarbonImmutable::now();
        $todayStart = $now->startOfDay();
        $todayEnd = $now->endOfDay();
        $weekStart = $now->subDays(6)->startOfDay();

        $invoiceBase = Invoice::query()
            ->select(['id', 'company_id', 'doc_no', 'status', 'currency', 'grand_total', 'issued_at', 'customer_id'])
            ->where('company_id', $companyId);

        $shipmentBase = Shipment::query()
            ->select(['id', 'company_id', 'doc_no', 'status', 'shipped_at', 'customer_id'])
            ->where('company_id', $companyId);

        $receiptBase = Receipt::query()
            ->select(['id', 'company_id', 'doc_no', 'amount', 'received_at', 'customer_id', 'method'])
            ->where('company_id', $companyId);

        $goodsReceiptBase = GoodsReceipt::query()
            ->select(['id', 'company_id', 'doc_no', 'status', 'received_at', 'warehouse_id'])
            ->where('company_id', $companyId);

        $kpis = [
            'issued_invoices_today' => (clone $invoiceBase)
                ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIALLY_PAID])
                ->whereBetween('issued_at', [$todayStart, $todayEnd])
                ->count(),
            'issued_invoices_week' => (clone $invoiceBase)
                ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIALLY_PAID])
                ->whereBetween('issued_at', [$weekStart, $todayEnd])
                ->count(),
            'receipts_today' => (clone $receiptBase)
                ->whereBetween('received_at', [$todayStart, $todayEnd])
                ->count(),
            'receipts_week' => (clone $receiptBase)
                ->whereBetween('received_at', [$weekStart, $todayEnd])
                ->count(),
            'shipments_today' => (clone $shipmentBase)
                ->whereBetween('shipped_at', [$todayStart, $todayEnd])
                ->count(),
            'shipments_week' => (clone $shipmentBase)
                ->whereBetween('shipped_at', [$weekStart, $todayEnd])
                ->count(),
            'goods_receipts_today' => (clone $goodsReceiptBase)
                ->whereBetween('received_at', [$todayStart, $todayEnd])
                ->count(),
            'goods_receipts_week' => (clone $goodsReceiptBase)
                ->whereBetween('received_at', [$weekStart, $todayEnd])
                ->count(),
        ];

        $recentInvoices = (clone $invoiceBase)
            ->with(['customer:id,name'])
            ->orderByDesc('issued_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentShipments = (clone $shipmentBase)
            ->with(['customer:id,name'])
            ->orderByDesc('shipped_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentGoodsReceipts = (clone $goodsReceiptBase)
            ->with(['warehouse:id,name'])
            ->orderByDesc('received_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentMedia = Media::query()
            ->select(['id', 'company_id', 'original_name', 'ext', 'mime', 'size', 'created_at'])
            ->where('company_id', $companyId)
            ->latest('created_at')
            ->limit(5)
            ->get();

        $pendingCloseout = (clone $invoiceBase)
            ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIALLY_PAID])
            ->whereBetween('issued_at', [$todayStart, $todayEnd])
            ->orderByDesc('issued_at')
            ->limit(5)
            ->get(['id', 'doc_no', 'issued_at']);

        return compact(
            'kpis',
            'recentInvoices',
            'recentShipments',
            'recentGoodsReceipts',
            'recentMedia',
            'pendingCloseout',
            'todayStart'
        );
    }
}
