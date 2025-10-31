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
            'logistics::shipments.index', 'logistics::admin.shipments.index' => $this->composeLogisticsShipments($data),
            'logistics::receipts.index', 'logistics::admin.receipts.index' => $this->composeLogisticsReceipts($data),
            'marketing::customers.index', 'marketing::admin.customers.index' => $this->composeMarketingCustomers($data),
            'marketing::orders.index', 'marketing::admin.orders.index' => $this->composeMarketingOrders($data),
            'production::admin.workorders.index' => $this->composeProductionWorkorders($data),
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
            ['key' => 'target', 'label' => 'Hedef', 'type' => 'text', 'sortable' => true],
            ['key' => 'due_date', 'label' => 'Termin', 'type' => 'date', 'sortable' => true, 'filterable' => true],
            ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
        ];

        $paginator = $data['workorders'] ?? $data['workOrders'];
        $items = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($paginator);

        $rows = $items->map(function ($workorder) {
            return [
                'id' => 'workorder-'.$workorder->id,
                'cells' => [
                    'doc_no' => $workorder->doc_no ?? $workorder->order_no ?? '—',
                    'product' => $workorder->product?->name ?? '—',
                    'status' => $workorder->status ?? 'draft',
                    'target' => [
                        'html' => e(number_format($workorder->target_qty ?? 0, 3) . ' ' . ($workorder->uom ?? '')),
                        'display' => number_format($workorder->target_qty ?? 0, 3) . ' ' . ($workorder->uom ?? ''),
                        'raw' => $workorder->target_qty ?? 0,
                    ],
                    'due_date' => $workorder->due_date ?? $workorder->created_at,
                    'actions' => [
                        [
                            'label' => 'Detay',
                            'href' => route('admin.production.workorders.show', $workorder),
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