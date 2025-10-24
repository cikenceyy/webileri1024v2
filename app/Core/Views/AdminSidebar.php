<?php

namespace App\Core\Views;

use Illuminate\Support\Facades\Gate;

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
            ...self::consoleSection(),
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
                        'label' => 'İK Yönetimi',
                        'icon' => 'bi bi-people',
                        'route' => 'admin.hr.employees.index',
                        'pattern' => 'admin/hr*',
                        'children' => [
                            ['label' => 'Personel Dizini', 'route' => 'admin.hr.employees.index', 'pattern' => 'admin/hr/employees*'],
                            ['label' => 'Personel Ayarları', 'route' => 'admin.hr.settings.departments.index', 'pattern' => 'admin/hr/settings*'],
                        ],
                    ],
                    [
                        'label' => 'Lojistik',
                        'icon' => 'bi bi-truck',
                        'route' => 'admin.logistics.shipments.index',
                        'pattern' => 'admin/logistics*',
                        'children' => [
                            ['label' => 'Sevkiyatlar', 'route' => 'admin.logistics.shipments.index', 'pattern' => 'admin/logistics/shipments*'],
                            ['label' => 'Mal Kabul (GRN)', 'route' => 'admin.logistics.receipts.index', 'pattern' => 'admin/logistics/receipts*'],
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

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function consoleSection(): array
    {
        $definitions = [
            [
                'key' => 'o2c',
                'label' => 'Order to Cash',
                'icon' => 'bi bi-repeat',
                'route' => 'admin.consoles.o2c.index',
                'pattern' => 'admin/consoles/o2c*',
                'gate' => 'viewO2CConsole',
            ],
            [
                'key' => 'p2p',
                'label' => 'Procure to Pay',
                'icon' => 'bi bi-basket',
                'route' => 'admin.consoles.p2p.index',
                'pattern' => 'admin/consoles/p2p*',
                'gate' => 'viewP2PConsole',
            ],
            [
                'key' => 'mto',
                'label' => 'Make to Order',
                'icon' => 'bi bi-diagram-3',
                'route' => 'admin.consoles.mto.index',
                'pattern' => 'admin/consoles/mto*',
                'gate' => 'viewMTOConsole',
            ],
            [
                'key' => 'replenish',
                'label' => 'Replenish',
                'icon' => 'bi bi-arrow-left-right',
                'route' => 'admin.consoles.replenish.index',
                'pattern' => 'admin/consoles/replenish*',
                'gate' => 'viewReplenishConsole',
            ],
            [
                'key' => 'returns',
                'label' => 'Returns',
                'icon' => 'bi bi-arrow-counterclockwise',
                'route' => 'admin.consoles.returns.index',
                'pattern' => 'admin/consoles/returns*',
                'gate' => 'viewReturnsConsole',
            ],
            [
                'key' => 'quality',
                'label' => 'Quality',
                'icon' => 'bi bi-shield-check',
                'route' => 'admin.consoles.quality.index',
                'pattern' => 'admin/consoles/quality*',
                'gate' => 'viewQualityConsole',
            ],
            [
                'key' => 'closeout',
                'label' => 'Closeout',
                'icon' => 'bi bi-printer',
                'route' => 'admin.consoles.closeout.index',
                'pattern' => 'admin/consoles/closeout*',
                'gate' => 'viewCloseoutConsole',
            ],
        ];

        $items = [];
        foreach ($definitions as $definition) {
            if (config('features.consoles.' . $definition['key'], true) && Gate::allows($definition['gate'])) {
                $items[] = [
                    'label' => $definition['label'],
                    'icon' => $definition['icon'],
                    'route' => $definition['route'],
                    'pattern' => $definition['pattern'],
                ];
            }
        }

        if (empty($items)) {
            return [];
        }

        return [[
            'title' => 'Konsollar',
            'description' => 'Operasyonel süreç panoları',
            'items' => $items,
        ]];
    }
}
