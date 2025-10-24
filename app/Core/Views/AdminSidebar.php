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
                        'children' => array_values(array_filter([
                            ['label' => 'Müşteriler', 'route' => 'admin.marketing.customers.index', 'pattern' => 'admin/marketing/customers*'],
                            ['label' => 'Siparişler', 'route' => 'admin.marketing.orders.index', 'pattern' => 'admin/marketing/orders*'],
                            ['label' => 'Fiyat Listeleri', 'route' => 'admin.marketing.pricelists.index', 'pattern' => 'admin/marketing/pricelists*'],
                            config('features.marketing.returns', true)
                                ? ['label' => 'İadeler (RMA)', 'route' => 'admin.marketing.returns.index', 'pattern' => 'admin/marketing/returns*']
                                : null,
                        ])),
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
                        'route' => 'admin.inventory.home',
                        'pattern' => 'admin/inventory*',
                        'children' => [
                            ['label' => 'Kontrol Kulesi', 'route' => 'admin.inventory.home', 'pattern' => 'admin/inventory'],
                            ['label' => 'Stok Konsolu', 'route' => 'admin.inventory.stock.console', 'pattern' => 'admin/inventory/stock/console*'],
                            ['label' => 'Ürünler', 'route' => 'admin.inventory.products.index', 'pattern' => 'admin/inventory/products*'],
                            ['label' => 'Depolar', 'route' => 'admin.inventory.warehouses.index', 'pattern' => 'admin/inventory/warehouses*'],
                            ['label' => 'Transferler', 'route' => 'admin.inventory.transfers.index', 'pattern' => 'admin/inventory/transfers*'],
                            ['label' => 'Sayım', 'route' => 'admin.inventory.counts.index', 'pattern' => 'admin/inventory/counts*'],
                            ['label' => 'Kategoriler', 'route' => 'admin.inventory.categories.index', 'pattern' => 'admin/inventory/categories*'],
                            ['label' => 'Ayarlar', 'route' => 'admin.inventory.settings.index', 'pattern' => 'admin/inventory/settings*'],
                        ],
                    ],
                    [
                        'label' => 'Üretim',
                        'icon' => 'bi bi-gear-wide-connected',
                        'route' => 'admin.production.workorders.index',
                        'pattern' => 'admin/production*',
                        'children' => [
                            ['label' => 'Üretim Emirleri', 'route' => 'admin.production.workorders.index', 'pattern' => 'admin/production/workorders*'],
                            ['label' => 'Ürün Reçeteleri', 'route' => 'admin.production.boms.index', 'pattern' => 'admin/production/boms*'],
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
                        'route' => 'admin.finance.invoices.index',
                        'pattern' => 'admin/finance*',
                        'children' => [
                            ['label' => 'Faturalar', 'route' => 'admin.finance.invoices.index', 'pattern' => 'admin/finance/invoices*'],
                            ['label' => 'Tahsilatlar', 'route' => 'admin.finance.receipts.index', 'pattern' => 'admin/finance/receipts*'],
                            ['label' => 'Cashbook (Lite)', 'route' => 'admin.finance.cashbook.index', 'pattern' => 'admin/finance/cashbook*'],
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
                    [
                        'label' => 'CMS Yönetimi',
                        'icon' => 'bi bi-browser-chrome',
                        'route' => 'cms.admin.pages.index',
                        'pattern' => 'admin/cms*',
                        'children' => [
                            ['label' => 'Canlı Editör', 'route' => 'cms.admin.editor', 'pattern' => 'admin/cms/editor'],
                            ['label' => 'Sayfa Yapısı', 'route' => 'cms.admin.pages.index', 'pattern' => 'admin/cms/*'],
                            ['label' => 'Form Mesajları', 'route' => 'cms.admin.messages.index', 'pattern' => 'admin/cms/messages*', 'badge_key' => 'cmsUnreadMessages'],
                        ],
                    ],
                ],
            ],
        ];
    }
}