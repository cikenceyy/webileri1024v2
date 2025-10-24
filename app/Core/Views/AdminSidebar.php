<?php

namespace App\Core\Views;

use App\Core\Support\Nav\NavGate;

class AdminSidebar
{
    /**
     * Build the navigation structure for the admin sidebar.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function navigation(): array
    {
        $sections = [
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
                        'abilities' => ['drive.view'],
                        'children' => [
                            ['label' => 'Medya Havuzu', 'route' => 'admin.drive.media.index', 'pattern' => 'admin/drive*', 'abilities' => ['drive.view']],
                        ],
                    ],
                    [
                        'label' => 'CRM & Pazarlama',
                        'icon' => 'bi bi-bullseye',
                        'route' => 'admin.marketing.index',
                        'pattern' => 'admin/marketing*',
                        'abilities' => ['marketing.orders.view'],
                        'children' => array_values(array_filter([
                            ['label' => 'Müşteriler', 'route' => 'admin.marketing.customers.index', 'pattern' => 'admin/marketing/customers*', 'abilities' => ['marketing.customers.view']],
                            ['label' => 'Siparişler', 'route' => 'admin.marketing.orders.index', 'pattern' => 'admin/marketing/orders*', 'abilities' => ['marketing.orders.view']],
                            ['label' => 'Fiyat Listeleri', 'route' => 'admin.marketing.pricelists.index', 'pattern' => 'admin/marketing/pricelists*', 'abilities' => ['marketing.pricelists.view']],
                            config('features.marketing.returns', true)
                                ? ['label' => 'İadeler (RMA)', 'route' => 'admin.marketing.returns.index', 'pattern' => 'admin/marketing/returns*', 'abilities' => ['marketing.returns.view']]
                                : null,
                        ])),
                    ],
                    [
                        'label' => 'Satın Alma',
                        'icon' => 'bi bi-bag-check',
                        'route' => 'admin.procurement.pos.index',
                        'pattern' => 'admin/procurement*',
                        'abilities' => ['procurement.view'],
                        'children' => [
                            ['label' => 'Satın Alma Siparişleri', 'route' => 'admin.procurement.pos.index', 'pattern' => 'admin/procurement/pos*', 'abilities' => ['procurement.view']],
                            ['label' => 'Mal Kabul', 'route' => 'admin.procurement.grns.index', 'pattern' => 'admin/procurement/grns*', 'abilities' => ['procurement.view']],
                        ],
                    ],
                    [
                        'label' => 'Envanter Yönetimi',
                        'icon' => 'bi bi-box-seam',
                        'route' => 'admin.inventory.home',
                        'pattern' => 'admin/inventory*',
                        'abilities' => ['inventory.stock.view'],
                        'children' => [
                            ['label' => 'Kontrol Kulesi', 'route' => 'admin.inventory.home', 'pattern' => 'admin/inventory', 'abilities' => ['inventory.stock.view']],
                            ['label' => 'Stok Konsolu', 'route' => 'admin.inventory.stock.console', 'pattern' => 'admin/inventory/stock/console*', 'abilities' => ['inventory.stock.view']],
                            ['label' => 'Ürünler', 'route' => 'admin.inventory.products.index', 'pattern' => 'admin/inventory/products*', 'abilities' => ['inventory.product.view']],
                            ['label' => 'Depolar', 'route' => 'admin.inventory.warehouses.index', 'pattern' => 'admin/inventory/warehouses*', 'abilities' => ['inventory.warehouse.view']],
                            ['label' => 'Transferler', 'route' => 'admin.inventory.transfers.index', 'pattern' => 'admin/inventory/transfers*', 'abilities' => ['inventory.transfer.view']],
                            ['label' => 'Sayım', 'route' => 'admin.inventory.counts.index', 'pattern' => 'admin/inventory/counts*', 'abilities' => ['inventory.count.view']],
                            ['label' => 'Kategoriler', 'route' => 'admin.inventory.categories.index', 'pattern' => 'admin/inventory/categories*', 'abilities' => ['inventory.category.view']],
                            ['label' => 'Ayarlar', 'route' => 'admin.inventory.settings.index', 'pattern' => 'admin/inventory/settings*', 'abilities' => ['inventory.warehouse.update']],
                        ],
                    ],
                    [
                        'label' => 'Üretim',
                        'icon' => 'bi bi-gear-wide-connected',
                        'route' => 'admin.production.workorders.index',
                        'pattern' => 'admin/production*',
                        'abilities' => ['production.workorders.view'],
                        'children' => [
                            ['label' => 'Üretim Emirleri', 'route' => 'admin.production.workorders.index', 'pattern' => 'admin/production/workorders*', 'abilities' => ['production.workorders.view']],
                            ['label' => 'Ürün Reçeteleri', 'route' => 'admin.production.boms.index', 'pattern' => 'admin/production/boms*', 'abilities' => ['production.boms.view']],
                        ],
                    ],
                    [
                        'label' => 'İK Yönetimi',
                        'icon' => 'bi bi-people',
                        'route' => 'admin.hr.employees.index',
                        'pattern' => 'admin/hr*',
                        'abilities' => ['hr.employees.view'],
                        'children' => [
                            ['label' => 'Personel Dizini', 'route' => 'admin.hr.employees.index', 'pattern' => 'admin/hr/employees*', 'abilities' => ['hr.employees.view']],
                            ['label' => 'Personel Ayarları', 'route' => 'admin.hr.settings.departments.index', 'pattern' => 'admin/hr/settings*', 'abilities' => ['hr.departments.view']],
                        ],
                    ],
                    [
                        'label' => 'Lojistik',
                        'icon' => 'bi bi-truck',
                        'route' => 'admin.logistics.shipments.index',
                        'pattern' => 'admin/logistics*',
                        'abilities' => ['logistics.shipments.view', 'logistics.receipts.view'],
                        'children' => [
                            ['label' => 'Sevkiyatlar', 'route' => 'admin.logistics.shipments.index', 'pattern' => 'admin/logistics/shipments*', 'abilities' => ['logistics.shipments.view']],
                            ['label' => 'Mal Kabul (GRN)', 'route' => 'admin.logistics.receipts.index', 'pattern' => 'admin/logistics/receipts*', 'abilities' => ['logistics.receipts.view']],
                        ],
                    ],
                    [
                        'label' => 'Finans',
                        'icon' => 'bi bi-cash-stack',
                        'route' => 'admin.finance.invoices.index',
                        'pattern' => 'admin/finance*',
                        'abilities' => ['finance.invoices.view'],
                        'children' => [
                            ['label' => 'Faturalar', 'route' => 'admin.finance.invoices.index', 'pattern' => 'admin/finance/invoices*', 'abilities' => ['finance.invoices.view']],
                            ['label' => 'Tahsilatlar', 'route' => 'admin.finance.receipts.index', 'pattern' => 'admin/finance/receipts*', 'abilities' => ['finance.receipts.view']],
                            ['label' => 'Cashbook (Lite)', 'route' => 'admin.finance.cashbook.index', 'pattern' => 'admin/finance/cashbook*', 'abilities' => ['finance.cashbook.view']],
                        ],
                    ],
                    [
                        'label' => 'Ayarlar',
                        'icon' => 'bi bi-gear',
                        'route' => 'admin.settings.company.edit',
                        'pattern' => 'admin/settings*',
                        'abilities' => ['settings.view'],
                        'children' => [
                            ['label' => 'Şirket Profili', 'route' => 'admin.settings.company.edit', 'pattern' => 'admin/settings/company*', 'abilities' => ['settings.view']],
                        ],
                    ],
                    [
                        'label' => 'CMS Yönetimi',
                        'icon' => 'bi bi-browser-chrome',
                        'route' => 'cms.admin.pages.index',
                        'pattern' => 'admin/cms*',
                        'abilities' => ['cms.manage'],
                        'children' => [
                            ['label' => 'Canlı Editör', 'route' => 'cms.admin.editor', 'pattern' => 'admin/cms/editor', 'abilities' => ['cms.manage']],
                            ['label' => 'Sayfa Yapısı', 'route' => 'cms.admin.pages.index', 'pattern' => 'admin/cms/*', 'abilities' => ['cms.manage']],
                            ['label' => 'Form Mesajları', 'route' => 'cms.admin.messages.index', 'pattern' => 'admin/cms/messages*', 'abilities' => ['cms.manage'], 'badge_key' => 'cmsUnreadMessages'],
                        ],
                    ],
                ],
            ],
        ];

        return self::filterSections($sections);
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
                'abilities' => ['gate:viewO2CConsole'],
            ],
            [
                'key' => 'p2p',
                'label' => 'Procure to Pay',
                'icon' => 'bi bi-basket',
                'route' => 'admin.consoles.p2p.index',
                'pattern' => 'admin/consoles/p2p*',
                'abilities' => ['gate:viewP2PConsole'],
            ],
            [
                'key' => 'mto',
                'label' => 'Make to Order',
                'icon' => 'bi bi-diagram-3',
                'route' => 'admin.consoles.mto.index',
                'pattern' => 'admin/consoles/mto*',
                'abilities' => ['gate:viewMTOConsole'],
            ],
            [
                'key' => 'replenish',
                'label' => 'Replenish',
                'icon' => 'bi bi-arrow-left-right',
                'route' => 'admin.consoles.replenish.index',
                'pattern' => 'admin/consoles/replenish*',
                'abilities' => ['gate:viewReplenishConsole'],
            ],
            [
                'key' => 'returns',
                'label' => 'Returns',
                'icon' => 'bi bi-arrow-counterclockwise',
                'route' => 'admin.consoles.returns.index',
                'pattern' => 'admin/consoles/returns*',
                'abilities' => ['gate:viewReturnsConsole'],
            ],
            [
                'key' => 'quality',
                'label' => 'Quality',
                'icon' => 'bi bi-shield-check',
                'route' => 'admin.consoles.quality.index',
                'pattern' => 'admin/consoles/quality*',
                'abilities' => ['gate:viewQualityConsole'],
            ],
            [
                'key' => 'closeout',
                'label' => 'Closeout',
                'icon' => 'bi bi-printer',
                'route' => 'admin.consoles.closeout.index',
                'pattern' => 'admin/consoles/closeout*',
                'abilities' => ['gate:viewCloseoutConsole'],
            ],
        ];

        $items = [];
        foreach ($definitions as $definition) {
            if (! NavGate::visible($definition['abilities'], 'consoles.' . $definition['key'])) {
                continue;
            }

            $items[] = [
                'label' => $definition['label'],
                'icon' => $definition['icon'],
                'route' => $definition['route'],
                'pattern' => $definition['pattern'],
                'abilities' => $definition['abilities'],
                'features' => 'consoles.' . $definition['key'],
            ];
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

    /**
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<int, array<string, mixed>>
     */
    private static function filterSections(array $sections): array
    {
        $filtered = [];

        foreach ($sections as $section) {
            $items = [];
            foreach ($section['items'] as $item) {
                $filteredItem = self::filterItem($item);
                if ($filteredItem) {
                    $items[] = $filteredItem;
                }
            }

            if (! empty($items)) {
                $section['items'] = $items;
                $filtered[] = $section;
            }
        }

        return $filtered;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private static function filterItem(array $item): ?array
    {
        $children = [];
        if (! empty($item['children']) && is_array($item['children'])) {
            foreach ($item['children'] as $child) {
                $filteredChild = self::filterItem($child);
                if ($filteredChild) {
                    $children[] = $filteredChild;
                }
            }
        }

        if (! NavGate::visible($item['abilities'] ?? null, $item['features'] ?? null)) {
            return null;
        }

        if (! empty($children)) {
            $item['children'] = $children;
        } else {
            unset($item['children']);
        }

        return $item;
    }
}
