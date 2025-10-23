<?php

namespace App\Core\Views;

class AdminSidebar
{
    /**
     * Build the navigation structure for the admin sidebar.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function navigation(): array
    {
        return [
            [
                'title' => 'Genel Bakış',
                'description' => 'Ana paneller ve hızlı erişim ekranları',
                'items' => [
                    [
                        'label' => 'Gösterge Paneli',
                        'icon' => 'bi bi-speedometer2',
                        'route' => 'admin.dashboard',
                        'pattern' => 'admin',
                    ],
                ],
            ],
            [
                'title' => 'Konsollar',
                'description' => 'Operasyonel süreç panoları',
                'items' => [
                    [
                        'label' => 'Operasyon Konsolları',
                        'icon' => 'bi bi-kanban',
                        'route' => 'consoles.today',
                        'pattern' => 'consoles*',
                        'children' => [
                            ['label' => 'Bugün Panosu', 'route' => 'consoles.today', 'pattern' => 'admin/consoles/today'],
                            ['label' => 'Order-to-Cash', 'route' => 'consoles.o2c', 'pattern' => 'admin/consoles/o2c*'],
                            ['label' => 'Procure-to-Pay', 'route' => 'consoles.p2p', 'pattern' => 'admin/consoles/p2p*'],
                            ['label' => 'Make-to-Order', 'route' => 'consoles.mto', 'pattern' => 'admin/consoles/mto*'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'İş Birimleri',
                'description' => 'Modül bazlı uygulama rotaları',
                'items' => [
                    [
                        'label' => 'Drive Merkezi',
                        'icon' => 'bi bi-cloud-arrow-down',
                        'route' => 'admin.drive.media.index',
                        'pattern' => 'admin/drive*',
                        'children' => [
                            ['label' => 'Medya Havuzu', 'route' => 'admin.drive.media.index', 'pattern' => 'admin/drive*'],
                        ],
                    ],
                    [
                        'label' => 'CRM & Pazarlama',
                        'icon' => 'bi bi-bullseye',
                        'route' => 'admin.marketing.index',
                        'pattern' => 'admin/marketing*',
                        'children' => [
                            ['label' => 'Genel Bakış', 'route' => 'admin.marketing.index', 'pattern' => 'admin/marketing'],
                            ['label' => 'Müşteriler', 'route' => 'admin.marketing.customers.index', 'pattern' => 'admin/marketing/customers*'],
                            ['label' => 'Fırsatlar', 'route' => 'admin.marketing.opportunities.index', 'pattern' => 'admin/marketing/opportunities*'],
                            ['label' => 'Teklifler', 'route' => 'admin.marketing.quotes.index', 'pattern' => 'admin/marketing/quotes*'],
                            ['label' => 'Siparişler', 'route' => 'admin.marketing.orders.index', 'pattern' => 'admin/marketing/orders*'],
                            ['label' => 'Satış Raporu', 'route' => 'admin.marketing.reports.sales', 'pattern' => 'admin/marketing/reports/sales*'],
                            ['label' => 'Aktivite Akışı', 'route' => 'admin.marketing.activities.index', 'pattern' => 'admin/marketing/activities*'],
                            ['label' => 'Müşteri İçe Aktarım', 'route' => 'admin.marketing.customers.import.form', 'pattern' => 'admin/marketing/import/customers*'],
                        ],
                    ],
                    [
                        'label' => 'Satın Alma',
                        'icon' => 'bi bi-bag-check',
                        'route' => 'admin.procurement.pos.index',
                        'pattern' => 'admin/procurement*',
                        'children' => [
                            ['label' => 'Satın Alma Siparişleri', 'route' => 'admin.procurement.pos.index', 'pattern' => 'admin/procurement/pos*'],
                            ['label' => 'Mal Kabul', 'route' => 'admin.procurement.grns.index', 'pattern' => 'admin/procurement/grns*'],
                        ],
                    ],
                    [
                        'label' => 'Envanter Yönetimi',
                        'icon' => 'bi bi-box-seam',
                        'route' => 'admin.inventory.products.index',
                        'pattern' => 'admin/inventory*',
                        'children' => [
                            ['label' => 'Ürün Kategorileri', 'route' => 'admin.inventory.categories.index', 'pattern' => 'admin/inventory/categories*'],
                            ['label' => 'Birimler', 'route' => 'admin.inventory.units.index', 'pattern' => 'admin/inventory/units*'],
                            ['label' => 'Fiyat Listeleri', 'route' => 'admin.inventory.pricelists.index', 'pattern' => 'admin/inventory/pricelists*'],
                            ['label' => 'Depolar', 'route' => 'admin.inventory.warehouses.index', 'pattern' => 'admin/inventory/warehouses*'],
                            ['label' => 'Ürünler', 'route' => 'admin.inventory.products.index', 'pattern' => 'admin/inventory/products*'],
                            ['label' => 'Ürün İçe Aktarım', 'route' => 'admin.inventory.import.products.form', 'pattern' => 'admin/inventory/import/products*'],
                            ['label' => 'Stok Anlık Görünüm', 'route' => 'admin.inventory.stock.index', 'pattern' => 'admin/inventory/stock'],
                            ['label' => 'Stok Giriş', 'route' => 'admin.inventory.stock.in.form', 'pattern' => 'admin/inventory/stock/in*'],
                            ['label' => 'Stok Çıkış', 'route' => 'admin.inventory.stock.out.form', 'pattern' => 'admin/inventory/stock/out*'],
                            ['label' => 'Stok Transfer', 'route' => 'admin.inventory.stock.transfer.form', 'pattern' => 'admin/inventory/stock/transfer*'],
                            ['label' => 'Stok Düzeltme', 'route' => 'admin.inventory.stock.adjust.form', 'pattern' => 'admin/inventory/stock/adjust*'],
                            ['label' => 'Stok Raporu - Envanter', 'route' => 'admin.inventory.reports.onhand', 'pattern' => 'admin/inventory/reports/onhand*'],
                            ['label' => 'Stok Raporu - Defter', 'route' => 'admin.inventory.reports.ledger', 'pattern' => 'admin/inventory/reports/ledger*'],
                            ['label' => 'Stok Raporu - Değerleme', 'route' => 'admin.inventory.reports.valuation', 'pattern' => 'admin/inventory/reports/valuation*'],
                        ],
                    ],
                    [
                        'label' => 'Üretim',
                        'icon' => 'bi bi-gear-wide-connected',
                        'route' => 'admin.production.work-orders.index',
                        'pattern' => 'admin/production*',
                        'children' => [
                            ['label' => 'Üretim Emirleri', 'route' => 'admin.production.work-orders.index', 'pattern' => 'admin/production/work-orders*'],
                        ],
                    ],
                    [
                        'label' => 'Lojistik',
                        'icon' => 'bi bi-truck',
                        'route' => 'admin.logistics.shipments.index',
                        'pattern' => 'admin/logistics*',
                        'children' => [
                            ['label' => 'Sevkiyatlar', 'route' => 'admin.logistics.shipments.index', 'pattern' => 'admin/logistics/shipments*'],
                            ['label' => 'Teslimat Raporu', 'route' => 'admin.logistics.reports.register', 'pattern' => 'admin/logistics/reports/register*'],
                        ],
                    ],
                    [
                        'label' => 'Finans & Muhasebe',
                        'icon' => 'bi bi-cash-coin',
                        'route' => 'admin.finance.home',
                        'pattern' => 'admin/finance*',
                        'children' => [
                            ['label' => 'Kontrol Merkezi', 'route' => 'admin.finance.home', 'pattern' => 'admin/finance'],
                            ['label' => 'Tahsilat Konsolu', 'route' => 'admin.finance.collections.index', 'pattern' => 'admin/finance/collections*'],
                            ['label' => 'Fatura Stüdyosu', 'route' => 'admin.finance.invoices.index', 'pattern' => 'admin/finance/invoices*'],
                            ['label' => 'Banka & Kasa Paneli', 'route' => 'admin.finance.cash-panel.index', 'pattern' => 'admin/finance/cash-panel*'],
                            ['label' => 'Tahsilatlar', 'route' => 'admin.finance.receipts.index', 'pattern' => 'admin/finance/receipts*'],
                        ],
                    ],
                    [
                        'label' => 'Ayarlar',
                        'icon' => 'bi bi-gear',
                        'route' => 'admin.settings.company.edit',
                        'pattern' => 'admin/settings*',
                        'children' => [
                            ['label' => 'Şirket Profili', 'route' => 'admin.settings.company.edit', 'pattern' => 'admin/settings/company*'],
                        ],
                    ],
                ],
            ],
        ];
    }
}