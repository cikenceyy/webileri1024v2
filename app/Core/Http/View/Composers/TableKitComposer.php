<?php

namespace App\Core\Http\View\Composers;

use App\Core\Support\TableKit\TableConfig;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use function e;
use function number_format;
use function optional;
use function route;
use function strip_tags;

class TableKitComposer
{
    public function compose(View $view): void
    {
        $data = $view->getData();

        $context = match ($view->name()) {
            'inventory::warehouses.index' => $this->composeInventoryWarehouses($data),
            'inventory::transfers.index' => $this->composeInventoryTransfers($data),
            'inventory::counts.index' => $this->composeInventoryCounts($data),
            'inventory::products.index' => $this->composeInventoryProducts($data),
            'finance::admin.invoices.index' => $this->composeFinanceInvoices($data),
            'finance::admin.receipts.index' => $this->composeFinanceReceipts($data),
            'finance::admin.cashbook.index' => $this->composeFinanceCashbook($data),
            'logistics::shipments.index', 'logistics::admin.shipments.index' => $this->composeLogisticsShipments($data),
            'logistics::receipts.index', 'logistics::admin.receipts.index' => $this->composeLogisticsReceipts($data),
            'marketing::customers.index', 'marketing::admin.customers.index' => $this->composeMarketingCustomers($data),
            'marketing::orders.index', 'marketing::admin.orders.index' => $this->composeMarketingOrders($data),
            'procurement::pos.index', 'procurement::admin.pos.index' => $this->composeProcurementPurchaseOrders($data),
            'procurement::grns.index', 'procurement::admin.grns.index' => $this->composeProcurementGoodsReceipts($data),
            'hr::admin.employees.index' => $this->composeHumanResourcesEmployees($data),
            'production::admin.workorders.index' => $this->composeProductionWorkorders($data),
            'production::boms.index','production::admin.boms.index' => $this->composeProductionBoms($data),
            default => null,
        };

        if ($context === null) {
            return;
        }

        foreach ($context as $key => $value) {
            $view->with($key, $value);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeInventoryWarehouses(array $data): ?array
    {
        if (! isset($data['stockItems'])) {
            return null;
        }

        $columns = [
            ['key' => 'product', 'label' => 'Ürün', 'type' => 'text', 'filterable' => true, 'sortable' => true],
            ['key' => 'bin', 'label' => 'Raf', 'type' => 'text', 'filterable' => true, 'hidden_xs' => true],
            ['key' => 'qty', 'label' => 'Miktar', 'type' => 'number', 'sortable' => true, 'options' => ['precision' => 2], 'hidden_xs' => true],
        ];

        $items = collect($data['stockItems']);

        $rows = $items->map(function (array $item) {
            $product = $item['product'] ?? null;
            $bin = $item['bin'] ?? null;

            $productHtml = $product
                ? sprintf('<div class="tablekit__cell-stack"><span class="tablekit__cell-strong">%s</span><span class="tablekit__cell-subtle">%s</span></div>', e($product->name ?? '—'), e($product->sku ?? ''))
                : '—';

            $binHtml = $bin
                ? sprintf('<div class="tablekit__cell-stack"><span class="tablekit__cell-strong">%s</span><span class="tablekit__cell-subtle">%s</span></div>', e($bin->code ?? '—'), e($bin->name ?? ''))
                : 'Genel stok';

            return [
                'id' => $product?->id ? 'warehouse-'.$product->id.'-'.$bin?->id : Str::uuid()->toString(),
                'cells' => [
                    'product' => [
                        'raw' => $product?->name,
                        'display' => strip_tags($productHtml),
                        'html' => $productHtml,
                    ],
                    'bin' => [
                        'raw' => $bin ? ($bin->code.' '.$bin->name) : 'Genel stok',
                        'display' => strip_tags($binHtml),
                        'html' => $binHtml,
                    ],
                    'qty' => [
                        'raw' => $item['qty'] ?? 0,
                        'display' => number_format((float) ($item['qty'] ?? 0), 2, ',', '.'),
                        'html' => e(number_format((float) ($item['qty'] ?? 0), 2, ',', '.')),
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $items->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeInventoryTransfers(array $data): ?array
    {
        if (! isset($data['transfers'])) {
            return null;
        }

        $columns = [
            ['key' => 'doc_no', 'label' => 'Belge No', 'type' => 'text', 'sortable' => true, 'filterable' => true],
            ['key' => 'from', 'label' => 'Kaynak', 'type' => 'text', 'filterable' => true],
            ['key' => 'to', 'label' => 'Hedef', 'type' => 'text', 'filterable' => true],
            ['key' => 'status', 'label' => 'Durum', 'type' => 'badge', 'filterable' => true, 'enum' => [
                'draft' => 'Taslak',
                'posted' => 'Tamamlandı',
            ]],
            ['key' => 'created_at', 'label' => 'Tarih', 'type' => 'date', 'sortable' => true, 'filterable' => true],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['transfers'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($transfer) {
            return [
                'id' => 'transfer-'.$transfer->id,
                'cells' => [
                    'doc_no' => $transfer->doc_no ?? '—',
                    'from' => $transfer->fromWarehouse?->name ?? '—',
                    'to' => $transfer->toWarehouse?->name ?? '—',
                    'status' => $transfer->status ?? 'draft',
                    'created_at' => $transfer->created_at,
                    'actions' => [
                        [
                            'label' => 'Görüntüle',
                            'href' => route('admin.inventory.transfers.show', $transfer),
                            'variant' => 'secondary',
                        ],
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $rows->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => $paginator instanceof LengthAwarePaginator ? $paginator : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeInventoryCounts(array $data): ?array
    {
        if (! isset($data['counts'])) {
            return null;
        }

        $columns = [
            ['key' => 'doc_no', 'label' => 'Belge No', 'type' => 'text', 'sortable' => true, 'filterable' => true],
            ['key' => 'warehouse', 'label' => 'Depo', 'type' => 'text', 'filterable' => true],
            ['key' => 'status', 'label' => 'Durum', 'type' => 'badge', 'filterable' => true, 'enum' => [
                'draft' => 'Taslak',
                'counting' => 'Sayım',
                'counted' => 'Sayım Tamamlandı',
                'reconciled' => 'Mutabık',
            ]],
            ['key' => 'counted_at', 'label' => 'Sayım Tarihi', 'type' => 'date', 'sortable' => true, 'filterable' => true],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['counts'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($count) {
            return [
                'id' => 'count-'.$count->id,
                'cells' => [
                    'doc_no' => $count->doc_no ?? '—',
                    'warehouse' => $count->warehouse?->name ?? '—',
                    'status' => $count->status ?? 'draft',
                    'counted_at' => $count->counted_at ?? $count->created_at,
                    'actions' => [
                        [
                            'label' => 'Görüntüle',
                            'href' => route('admin.inventory.counts.show', $count),
                            'variant' => 'secondary',
                        ],
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $rows->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => $paginator instanceof LengthAwarePaginator ? $paginator : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeInventoryProducts(array $data): ?array
    {
        if (! isset($data['products'])) {
            return null;
        }

        $columns = [
            ['key' => 'name', 'label' => 'Ürün', 'type' => 'text', 'filterable' => true, 'sortable' => true],
            ['key' => 'sku', 'label' => 'SKU', 'type' => 'text', 'filterable' => true],
            ['key' => 'stock', 'label' => 'Stok', 'type' => 'number', 'sortable' => true, 'options' => ['precision' => 2]],
            ['key' => 'price', 'label' => 'Fiyat', 'type' => 'money', 'sortable' => true],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['products'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($product) {
            $stockSum = $product->stockItems?->sum('qty') ?? 0;
            $price = $product->price ?? 0;

            return [
                'id' => 'product-'.$product->id,
                'cells' => [
                    'name' => $product->name ?? '—',
                    'sku' => $product->sku ?? '—',
                    'stock' => $stockSum,
                    'price' => ['amount' => $price, 'currency' => optional($product->currency)->code ?? '₺'],
                    'actions' => [
                        [
                            'label' => 'Detay',
                            'href' => route('admin.inventory.products.show', $product),
                            'variant' => 'primary',
                        ],
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $rows->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => $paginator instanceof LengthAwarePaginator ? $paginator : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeFinanceInvoices(array $data): ?array
    {
        if (! isset($data['invoices'])) {
            return null;
        }

        $columns = [
            ['key' => 'doc_no', 'label' => 'Belge No', 'type' => 'text', 'sortable' => true, 'filterable' => true],
            ['key' => 'customer', 'label' => 'Müşteri', 'type' => 'text', 'filterable' => true],
            ['key' => 'status', 'label' => 'Durum', 'type' => 'badge', 'filterable' => true, 'enum' => [
                'draft' => 'Taslak',
                'issued' => 'Düzenlendi',
                'partially_paid' => 'Kısmi Ödendi',
                'paid' => 'Ödendi',
            ]],
            ['key' => 'grand_total', 'label' => 'Genel Toplam', 'type' => 'money', 'sortable' => true],
            ['key' => 'paid_amount', 'label' => 'Ödenen', 'type' => 'money', 'sortable' => true],
            ['key' => 'due_date', 'label' => 'Vade Tarihi', 'type' => 'date', 'sortable' => true, 'filterable' => true],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['invoices'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($invoice) {
            return [
                'id' => 'invoice-'.$invoice->id,
                'cells' => [
                    'doc_no' => $invoice->doc_no ?? 'Taslak',
                    'customer' => $invoice->customer?->name ?? '—',
                    'status' => $invoice->status ?? 'draft',
                    'grand_total' => ['amount' => $invoice->grand_total ?? 0, 'currency' => $invoice->currency ?? '₺'],
                    'paid_amount' => ['amount' => $invoice->paid_amount ?? 0, 'currency' => $invoice->currency ?? '₺'],
                    'due_date' => $invoice->due_date,
                    'actions' => [
                        [
                            'label' => 'Görüntüle',
                            'href' => route('admin.finance.invoices.show', $invoice),
                            'variant' => 'primary',
                        ],
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $rows->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => $paginator instanceof LengthAwarePaginator ? $paginator : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeFinanceReceipts(array $data): ?array
    {
        if (! isset($data['receipts'])) {
            return null;
        }

        $columns = [
            ['key' => 'doc_no', 'label' => 'Makbuz No', 'type' => 'text', 'sortable' => true, 'filterable' => true],
            ['key' => 'customer', 'label' => 'Müşteri', 'type' => 'text', 'filterable' => true],
            ['key' => 'status', 'label' => 'Durum', 'type' => 'badge', 'filterable' => true, 'enum' => [
                'draft' => 'Taslak',
                'posted' => 'Kaydedildi',
                'reconciled' => 'Mutabık',
            ]],
            ['key' => 'amount', 'label' => 'Tutar', 'type' => 'money', 'sortable' => true],
            ['key' => 'applied_amount', 'label' => 'Mahsup Edilen', 'type' => 'money', 'sortable' => true],
            ['key' => 'received_at', 'label' => 'Tahsil Tarihi', 'type' => 'date', 'sortable' => true, 'filterable' => true],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['receipts'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($receipt) {
            return [
                'id' => 'receipt-'.$receipt->id,
                'cells' => [
                    'doc_no' => $receipt->doc_no ?? '—',
                    'customer' => $receipt->customer?->name ?? '—',
                    'status' => $receipt->status ?? 'draft',
                    'amount' => ['amount' => $receipt->amount ?? 0, 'currency' => $receipt->currency ?? '₺'],
                    'applied_amount' => ['amount' => method_exists($receipt, 'appliedTotal') ? $receipt->appliedTotal() : 0, 'currency' => $receipt->currency ?? '₺'],
                    'received_at' => $receipt->received_at ?? $receipt->created_at,
                    'actions' => [
                        [
                            'label' => 'Görüntüle',
                            'href' => route('admin.finance.receipts.show', $receipt),
                            'variant' => 'primary',
                        ],
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $rows->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => $paginator instanceof LengthAwarePaginator ? $paginator : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeLogisticsShipments(array $data): ?array
    {
        if (! isset($data['shipments'])) {
            return null;
        }

        $columns = [
            ['key' => 'doc_no', 'label' => 'Belge No', 'type' => 'text', 'sortable' => true, 'filterable' => true],
            ['key' => 'customer', 'label' => 'Müşteri', 'type' => 'text', 'filterable' => true],
            ['key' => 'status', 'label' => 'Durum', 'type' => 'badge', 'filterable' => true, 'enum' => [
                'draft' => 'Taslak',
                'picking' => 'Toplanıyor',
                'packed' => 'Paketlendi',
                'shipped' => 'Sevk Edildi',
                'closed' => 'Kapandı',
                'cancelled' => 'İptal',
            ]],
            ['key' => 'packages', 'label' => 'Paket', 'type' => 'number', 'sortable' => true],
            ['key' => 'shipped_at', 'label' => 'Sevk Tarihi', 'type' => 'date', 'sortable' => true, 'filterable' => true],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['shipments'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($shipment) {
            return [
                'id' => 'shipment-'.$shipment->id,
                'cells' => [
                    'doc_no' => $shipment->doc_no ?? '—',
                    'customer' => $shipment->customer?->name ?? '—',
                    'status' => $shipment->status ?? 'draft',
                    'packages' => $shipment->packages_count ?? 0,
                    'shipped_at' => $shipment->shipped_at ?? $shipment->created_at,
                    'actions' => [
                        [
                            'label' => 'Detay',
                            'href' => route('admin.logistics.shipments.show', $shipment),
                            'variant' => 'secondary',
                        ],
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $rows->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => $paginator instanceof LengthAwarePaginator ? $paginator : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeLogisticsReceipts(array $data): ?array
    {
        if (! isset($data['receipts'])) {
            return null;
        }

        $columns = [
            ['key' => 'doc_no', 'label' => 'Belge No', 'type' => 'text', 'sortable' => true, 'filterable' => true],
            ['key' => 'supplier', 'label' => 'Tedarikçi', 'type' => 'text', 'filterable' => true],
            ['key' => 'status', 'label' => 'Durum', 'type' => 'badge', 'filterable' => true, 'enum' => [
                'draft' => 'Taslak',
                'receiving' => 'Teslim Alınıyor',
                'received' => 'Teslim Alındı',
                'reconciled' => 'Uzlaşıldı',
                'closed' => 'Kapandı',
                'cancelled' => 'İptal',
            ]],
            ['key' => 'warehouse', 'label' => 'Depo', 'type' => 'text', 'filterable' => true],
            ['key' => 'received_at', 'label' => 'Teslim Tarihi', 'type' => 'date', 'sortable' => true, 'filterable' => true],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['receipts'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($receipt) {
            return [
                'id' => 'log-receipt-'.$receipt->id,
                'cells' => [
                    'doc_no' => $receipt->doc_no ?? '—',
                    'supplier' => $receipt->supplier?->name ?? ($receipt->vendor_id ? ('#'.$receipt->vendor_id) : '—'),
                    'status' => $receipt->status ?? 'draft',
                    'warehouse' => $receipt->warehouse?->name ?? '—',
                    'received_at' => $receipt->received_at ?? $receipt->created_at,
                    'actions' => [
                        [
                            'label' => 'Detay',
                            'href' => route('admin.logistics.receipts.show', $receipt),
                            'variant' => 'secondary',
                        ],
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $rows->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => $paginator instanceof LengthAwarePaginator ? $paginator : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeMarketingCustomers(array $data): ?array
    {
        if (! isset($data['customers'])) {
            return null;
        }

        $columns = [
            ['key' => 'name', 'label' => 'Ad / Ünvan', 'type' => 'text', 'sortable' => true, 'filterable' => true],
            ['key' => 'email', 'label' => 'E-posta', 'type' => 'text', 'filterable' => true],
            ['key' => 'payment_terms', 'label' => 'Vade (gün)', 'type' => 'number', 'sortable' => true],
            ['key' => 'price_list', 'label' => 'Varsayılan Fiyat Listesi', 'type' => 'text', 'filterable' => true, 'hidden_xs' => true],
            ['key' => 'status', 'label' => 'Durum', 'type' => 'badge', 'filterable' => true, 'enum' => [
                'active' => 'Aktif',
                'inactive' => 'Pasif',
            ]],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['customers'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($customer) {
            return [
                'id' => 'customer-'.$customer->id,
                'cells' => [
                    'name' => $customer->name ?? '—',
                    'email' => $customer->email ?? '—',
                    'payment_terms' => $customer->payment_terms_days ?? 0,
                    'price_list' => $customer->priceList?->name ?? '—',
                    'status' => $customer->is_active ? 'active' : 'inactive',
                    'actions' => [
                        [
                            'label' => 'Profil',
                            'href' => route('admin.marketing.customers.show', $customer),
                            'variant' => 'secondary',
                        ],
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $rows->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => $paginator instanceof LengthAwarePaginator ? $paginator : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeMarketingOrders(array $data): ?array
    {
        if (! isset($data['orders'])) {
            return null;
        }

        $columns = [
            ['key' => 'doc_no', 'label' => 'Belge No', 'type' => 'text', 'sortable' => true, 'filterable' => true],
            ['key' => 'customer', 'label' => 'Müşteri', 'type' => 'text', 'filterable' => true],
            ['key' => 'status', 'label' => 'Durum', 'type' => 'badge', 'filterable' => true, 'enum' => [
                'draft' => 'Taslak',
                'confirmed' => 'Onaylı',
                'fulfilled' => 'Tamamlandı',
                'cancelled' => 'İptal',
            ]],
            ['key' => 'due_date', 'label' => 'Vade', 'type' => 'date', 'sortable' => true, 'filterable' => true],
            ['key' => 'created_at', 'label' => 'Oluşturulma', 'type' => 'date', 'sortable' => true, 'filterable' => true],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['orders'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($order) {
            return [
                'id' => 'order-'.$order->id,
                'cells' => [
                    'doc_no' => $order->doc_no ?? $order->order_no ?? '—',
                    'customer' => $order->customer?->name ?? '—',
                    'status' => $order->status ?? 'draft',
                    'due_date' => $order->due_date ?? null,
                    'created_at' => $order->created_at ?? $order->placed_at,
                    'actions' => [
                        [
                            'label' => 'Görüntüle',
                            'href' => route('admin.marketing.orders.show', $order),
                            'variant' => 'primary',
                        ],
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $rows->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => $paginator instanceof LengthAwarePaginator ? $paginator : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeProductionWorkorders(array $data): ?array
    {
        if (! isset($data['workorders']) && ! isset($data['workOrders'])) {
            return null;
        }

        $columns = [
            ['key' => 'doc_no', 'label' => 'Numara', 'type' => 'text', 'sortable' => true, 'filterable' => true],
            ['key' => 'product', 'label' => 'Ürün', 'type' => 'text', 'filterable' => true],
            ['key' => 'status', 'label' => 'Durum', 'type' => 'badge', 'filterable' => true, 'enum' => [
                'draft' => 'Taslak',
                'released' => 'Serbest',
                'in_progress' => 'Üretimde',
                'completed' => 'Tamamlandı',
                'closed' => 'Kapalı',
                'cancelled' => 'İptal',
            ]],
            ['key' => 'planned', 'label' => 'Planlanan', 'type' => 'number', 'sortable' => true, 'options' => ['precision' => 0]],
            ['key' => 'due_date', 'label' => 'Termin', 'type' => 'date', 'sortable' => true, 'filterable' => true],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['workorders'] ?? $data['workOrders'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($workorder) {
            $quantity = $workorder->planned_qty ?? $workorder->target_qty ?? 0;
            $uom = $workorder->uom ?? ($workorder->product?->unit ?? '');

            return [
                'id' => 'workorder-'.$workorder->id,
                'cells' => [
                    'doc_no' => $workorder->doc_no ?? $workorder->order_no ?? '—',
                    'product' => $workorder->product?->name ?? '—',
                    'status' => $workorder->status ?? 'draft',
                    'planned' => [
                        'raw' => (float) $quantity,
                        'display' => number_format((float) $quantity, 0, ',', '.'),
                        'html' => e(number_format((float) $quantity, 0, ',', '.').' '.$uom),
                    ],
                    'due_date' => $workorder->due_date ?? $workorder->created_at,
                    'actions' => [
                        [
                            'label' => 'Detay',
                            'href' => route('admin.production.workorders.show', $workorder),
                            'variant' => 'secondary',
                        ],
                        [
                            'label' => 'Düzenle',
                            'href' => route('admin.production.workorders.edit', $workorder),
                            'variant' => 'ghost',
                        ],
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $rows->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => $paginator instanceof LengthAwarePaginator ? $paginator : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeProductionBoms(array $data): ?array
    {
        if (! isset($data['boms'])) {
            return null;
        }

        $columns = [
            ['key' => 'code', 'label' => 'Kod', 'type' => 'text', 'sortable' => true, 'filterable' => true],
            ['key' => 'product', 'label' => 'Ürün', 'type' => 'text', 'filterable' => true],
            ['key' => 'version', 'label' => 'Versiyon', 'type' => 'text', 'sortable' => true],
            ['key' => 'output', 'label' => 'Çıktı', 'type' => 'number', 'sortable' => true, 'options' => ['precision' => 3]],
            ['key' => 'status', 'label' => 'Durum', 'type' => 'badge', 'filterable' => true, 'enum' => [
                'active' => 'Aktif',
                'inactive' => 'Pasif',
            ]],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['boms'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($bom) {
            $product = $bom->product;
            $unit = $product?->unit ?? 'adet';

            return [
                'id' => 'bom-'.$bom->id,
                'cells' => [
                    'code' => $bom->code ?? '—',
                    'product' => $product?->name ?? '—',
                    'version' => $bom->version ?? '—',
                    'output' => [
                        'raw' => (float) ($bom->output_qty ?? 0),
                        'display' => number_format((float) ($bom->output_qty ?? 0), 3, ',', '.').' '.$unit,
                        'html' => e(number_format((float) ($bom->output_qty ?? 0), 3, ',', '.').' '.$unit),
                    ],
                    'status' => $bom->is_active ? 'active' : 'inactive',
                    'actions' => [
                        [
                            'label' => 'Görüntüle',
                            'href' => route('admin.production.boms.show', $bom),
                            'variant' => 'secondary',
                        ],
                        [
                            'label' => 'Düzenle',
                            'href' => route('admin.production.boms.edit', $bom),
                            'variant' => 'ghost',
                        ],
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $rows->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => $paginator instanceof LengthAwarePaginator ? $paginator : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeHumanResourcesEmployees(array $data): ?array
    {
        if (! isset($data['employees'])) {
            return null;
        }

        $departmentOptions = collect($data['departments'] ?? [])->mapWithKeys(function ($label, $id) {
            return [(string) $id => (string) $label];
        });
        $titleOptions = collect($data['titles'] ?? [])->mapWithKeys(function ($label, $id) {
            return [(string) $id => (string) $label];
        });
        $employmentOptions = collect($data['employmentTypes'] ?? [])->mapWithKeys(function ($label, $id) {
            return [(string) $id => (string) $label];
        });

        $columns = [
            ['key' => 'code', 'label' => 'Kod', 'type' => 'text', 'sortable' => true, 'filterable' => true],
            ['key' => 'name', 'label' => 'Ad Soyad', 'type' => 'text', 'filterable' => true],
            ['key' => 'department', 'label' => 'Departman', 'type' => 'enum', 'filterable' => true, 'enum' => $departmentOptions->all()],
            ['key' => 'title', 'label' => 'Ünvan', 'type' => 'enum', 'filterable' => true, 'enum' => $titleOptions->all()],
            ['key' => 'employment_type', 'label' => 'Çalışma Tipi', 'type' => 'enum', 'filterable' => true, 'enum' => $employmentOptions->all()],
            ['key' => 'status', 'label' => 'Durum', 'type' => 'badge', 'filterable' => true, 'enum' => [
                'active' => 'Aktif',
                'inactive' => 'Pasif',
            ]],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['employees'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($employee) {
            $nameHtml = sprintf(
                '<div class="tablekit__cell-stack"><span class="tablekit__cell-strong">%s</span><span class="tablekit__cell-subtle">%s</span></div>',
                e($employee->name ?? '—'),
                e($employee->email ?? '—')
            );

            return [
                'id' => 'employee-'.$employee->id,
                'cells' => [
                    'code' => $employee->code ?? '—',
                    'name' => [
                        'raw' => $employee->name ?? '—',
                        'display' => strip_tags($nameHtml),
                        'html' => $nameHtml,
                    ],
                    'department' => (string) ($employee->department?->id ?? ''),
                    'title' => (string) ($employee->title?->id ?? ''),
                    'employment_type' => (string) ($employee->employmentType?->id ?? ''),
                    'status' => $employee->is_active ? 'active' : 'inactive',
                    'actions' => [
                        [
                            'label' => 'Görüntüle',
                            'href' => route('admin.hr.employees.show', $employee),
                            'variant' => 'secondary',
                        ],
                        [
                            'label' => 'Düzenle',
                            'href' => route('admin.hr.employees.edit', $employee),
                            'variant' => 'ghost',
                        ],
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $rows->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => $paginator instanceof LengthAwarePaginator ? $paginator : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeFinanceCashbook(array $data): ?array
    {
        if (! isset($data['entries'])) {
            return null;
        }

        $directionOptions = collect($data['directions'] ?? [])->mapWithKeys(function ($direction) {
            return [$direction => Str::headline($direction)];
        });

        $columns = [
            ['key' => 'occurred_at', 'label' => 'Tarih', 'type' => 'date', 'sortable' => true, 'filterable' => true],
            ['key' => 'direction', 'label' => 'Yön', 'type' => 'badge', 'filterable' => true, 'enum' => $directionOptions->all()],
            ['key' => 'account', 'label' => 'Hesap', 'type' => 'text', 'filterable' => true],
            ['key' => 'reference', 'label' => 'Referans', 'type' => 'text', 'filterable' => true],
            ['key' => 'amount', 'label' => 'Tutar', 'type' => 'money', 'sortable' => true, 'options' => ['currency' => 'TRY']],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['entries'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($entry) {
            $reference = $entry->reference_type
                ? sprintf('%s #%s', Str::headline($entry->reference_type), $entry->reference_id)
                : '—';

            return [
                'id' => 'cashbook-'.$entry->id,
                'cells' => [
                    'occurred_at' => $entry->occurred_at ?? $entry->created_at,
                    'direction' => $entry->direction ?? 'in',
                    'account' => $entry->account ?? '—',
                    'reference' => $reference,
                    'amount' => [
                        'raw' => (float) ($entry->amount ?? 0),
                        'display' => number_format((float) ($entry->amount ?? 0), 2, ',', '.'),
                        'html' => e(number_format((float) ($entry->amount ?? 0), 2, ',', '.').' '.($entry->currency ?? 'TRY')),
                    ],
                    'actions' => [
                        [
                            'label' => 'Görüntüle',
                            'href' => route('admin.finance.cashbook.show', $entry),
                            'variant' => 'secondary',
                        ],
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $rows->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => $paginator instanceof LengthAwarePaginator ? $paginator : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeProcurementPurchaseOrders(array $data): ?array
    {
        if (! isset($data['purchaseOrders'])) {
            return null;
        }

        $columns = [
            ['key' => 'po_number', 'label' => 'Sipariş', 'type' => 'text', 'sortable' => true, 'filterable' => true],
            ['key' => 'supplier', 'label' => 'Tedarikçi', 'type' => 'text', 'filterable' => true],
            ['key' => 'status', 'label' => 'Durum', 'type' => 'badge', 'filterable' => true, 'enum' => [
                'draft' => 'Taslak',
                'approved' => 'Onaylandı',
                'closed' => 'Kapandı',
            ]],
            ['key' => 'total', 'label' => 'Toplam', 'type' => 'money', 'sortable' => true, 'options' => ['currency' => 'TRY']],
            ['key' => 'created_at', 'label' => 'Oluşturulma', 'type' => 'date', 'sortable' => true, 'filterable' => true],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['purchaseOrders'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($purchaseOrder) {
            $supplierRelation = $purchaseOrder->supplier ?? null;
            $supplierName = is_object($supplierRelation) && isset($supplierRelation->name)
                ? $supplierRelation->name
                : null;
            $supplierLabel = $supplierName ?: ('Tedarikçi #'.$purchaseOrder->supplier_id);

            return [
                'id' => 'po-'.$purchaseOrder->id,
                'cells' => [
                    'po_number' => $purchaseOrder->po_number ?? ('#'.$purchaseOrder->id),
                    'supplier' => $supplierLabel,
                    'status' => $purchaseOrder->status ?? 'draft',
                    'total' => [
                        'raw' => (float) ($purchaseOrder->total ?? 0),
                        'display' => number_format((float) ($purchaseOrder->total ?? 0), 2, ',', '.').' '.($purchaseOrder->currency ?? 'TRY'),
                        'html' => e(number_format((float) ($purchaseOrder->total ?? 0), 2, ',', '.').' '.($purchaseOrder->currency ?? 'TRY')),
                    ],
                    'created_at' => $purchaseOrder->created_at,
                    'actions' => [
                        [
                            'label' => 'Görüntüle',
                            'href' => route('admin.procurement.pos.show', $purchaseOrder),
                            'variant' => 'secondary',
                        ],
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $rows->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => $paginator instanceof LengthAwarePaginator ? $paginator : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function composeProcurementGoodsReceipts(array $data): ?array
    {
        if (! isset($data['goodsReceipts'])) {
            return null;
        }

        $columns = [
            ['key' => 'grn', 'label' => 'Mal Kabul', 'type' => 'text', 'sortable' => true, 'filterable' => true],
            ['key' => 'purchase_order', 'label' => 'Sipariş', 'type' => 'text', 'filterable' => true],
            ['key' => 'status', 'label' => 'Durum', 'type' => 'badge', 'filterable' => true, 'enum' => [
                'partial' => 'Kısmi',
                'received' => 'Tamamlandı',
            ]],
            ['key' => 'received_at', 'label' => 'Teslim Tarihi', 'type' => 'date', 'sortable' => true, 'filterable' => true],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['goodsReceipts'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($grn) {
            return [
                'id' => 'grn-'.$grn->id,
                'cells' => [
                    'grn' => '#'.$grn->id,
                    'purchase_order' => $grn->purchaseOrder?->po_number ?? ('#'.$grn->purchase_order_id),
                    'status' => $grn->status ?? 'partial',
                    'received_at' => $grn->received_at ?? $grn->created_at,
                    'actions' => [
                        [
                            'label' => 'Görüntüle',
                            'href' => route('admin.procurement.grns.show', $grn),
                            'variant' => 'secondary',
                        ],
                    ],
                ],
            ];
        });

        $config = TableConfig::make($columns, [
            'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $rows->count(),
        ]);

        return [
            'tableKitConfig' => $config,
            'tableKitRows' => $rows->values()->all(),
            'tableKitPaginator' => $paginator instanceof LengthAwarePaginator ? $paginator : null,
        ];
    }
}