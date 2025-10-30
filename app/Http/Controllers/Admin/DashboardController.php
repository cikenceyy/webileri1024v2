<?php

namespace App\Http\Controllers\Admin;

use App\Core\Cache\InvalidationService;
use App\Core\Cache\Keys;
use App\Http\Controllers\Controller;
use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use App\Modules\Logistics\Domain\Models\Shipment;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use function currentCompanyId;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $companyId = currentCompanyId();
        $user = $request->user();

        $roleFlags = [
            'owner' => $user?->hasRole('owner') || $user?->hasRole('super_admin'),
            'accountant' => $user?->hasRole('accountant'),
            'intern' => $user?->hasRole('intern'),
            'super_admin' => $user?->hasRole('super_admin'),
        ];

        $cache = app(InvalidationService::class);
        $cacheKey = Keys::forTenant($companyId ?: 0, ['dashboard', 'summary'], 'v1');
        $ttl = (int) config('cache.ttl_profiles.warm', 900);

        $payload = $cache->rememberWithTags($cacheKey, [sprintf('tenant:%d', $companyId ?: 0), 'dashboard'], $ttl, function () use ($companyId) {
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
        });

        $alerts = [];
        if ($payload['pendingCloseout']->isNotEmpty()) {
            $alerts[] = [
                'type' => 'warning',
                'title' => __('Yazdırma bekleyen faturalar var'),
                'description' => __('Bugün kesilen :count fatura Closeout üzerinden yazdırılmadı.', [
                    'count' => $payload['pendingCloseout']->count(),
                ]),
                'action' => route('admin.consoles.closeout.index', [
                    'date' => $payload['todayStart']->toDateString(),
                ]),
                'items' => $payload['pendingCloseout']->map(fn ($invoice) => $invoice->doc_no ?: __('Taslak'))->all(),
            ];
        }

        $quickActions = $this->resolveQuickActions($roleFlags);

        $kpiCards = $this->formatKpis($payload['kpis'], $roleFlags);

        return view('admin.dashboard.index', [
            'kpiCards' => $kpiCards,
            'recentInvoices' => $payload['recentInvoices'],
            'recentShipments' => $payload['recentShipments'],
            'recentGoodsReceipts' => $payload['recentGoodsReceipts'],
            'recentMedia' => $payload['recentMedia'],
            'alerts' => $alerts,
            'roleFlags' => $roleFlags,
            'quickActions' => $quickActions,
        ]);
    }

    /**
     * @param  array{issued_invoices_today:int,issued_invoices_week:int,receipts_today:int,receipts_week:int,shipments_today:int,shipments_week:int,goods_receipts_today:int,goods_receipts_week:int}  $kpis
     * @param  array{owner:bool,accountant:bool,intern:bool,super_admin:bool}  $roleFlags
     */
    protected function formatKpis(array $kpis, array $roleFlags): array
    {
        $cards = [
            [
                'key' => 'invoices',
                'label' => __('Kesilen Faturalar'),
                'today' => $kpis['issued_invoices_today'],
                'week' => $kpis['issued_invoices_week'],
                'icon' => 'bi bi-receipt-cutoff',
            ],
            [
                'key' => 'receipts',
                'label' => __('Tahsilatlar'),
                'today' => $kpis['receipts_today'],
                'week' => $kpis['receipts_week'],
                'icon' => 'bi bi-cash-stack',
            ],
            [
                'key' => 'shipments',
                'label' => __('Sevk Edilen Sipariş'),
                'today' => $kpis['shipments_today'],
                'week' => $kpis['shipments_week'],
                'icon' => 'bi bi-truck',
            ],
            [
                'key' => 'goods_receipts',
                'label' => __('Alınan GRN'),
                'today' => $kpis['goods_receipts_today'],
                'week' => $kpis['goods_receipts_week'],
                'icon' => 'bi bi-box-arrow-in-down',
            ],
        ];

        if ($roleFlags['accountant'] && ! $roleFlags['owner'] && ! $roleFlags['super_admin']) {
            $cards = Arr::where($cards, fn ($card) => in_array($card['key'], ['invoices', 'receipts'], true));
        }

        return array_values($cards);
    }

    /**
     * @param  array{owner:bool,accountant:bool,intern:bool,super_admin:bool}  $roleFlags
     */
    protected function resolveQuickActions(array $roleFlags): array
    {
        $actions = [
            [
                'key' => 'order',
                'label' => __('Yeni Sipariş'),
                'route' => 'admin.marketing.orders.create',
                'icon' => 'bi bi-cart-plus',
            ],
            [
                'key' => 'ship',
                'label' => __('Sevk Et'),
                'route' => 'admin.logistics.shipments.create',
                'icon' => 'bi bi-box-arrow-up',
            ],
            [
                'key' => 'invoice',
                'label' => __('Faturalandır'),
                'route' => 'admin.finance.invoices.create',
                'icon' => 'bi bi-receipt',
            ],
            [
                'key' => 'collect',
                'label' => __('Tahsil Et'),
                'route' => 'admin.finance.receipts.create',
                'icon' => 'bi bi-wallet2',
            ],
            [
                'key' => 'grn',
                'label' => __('GRN Oluştur'),
                'route' => 'admin.logistics.receipts.create',
                'icon' => 'bi bi-box-arrow-in-down',
            ],
            [
                'key' => 'wo',
                'label' => __('WO Oluştur'),
                'route' => 'admin.production.workorders.create',
                'icon' => 'bi bi-gear',
            ],
        ];

        if ($roleFlags['accountant'] && ! $roleFlags['owner'] && ! $roleFlags['super_admin']) {
            $actions = Arr::where($actions, fn ($action) => in_array($action['key'], ['invoice', 'collect'], true));
        }

        $disabled = $roleFlags['intern'];

        return array_map(function ($action) use ($disabled) {
            $action['disabled'] = $disabled;
            return $action;
        }, array_values($actions));
    }
}