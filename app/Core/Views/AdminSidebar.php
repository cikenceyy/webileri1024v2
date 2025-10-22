<?php

namespace App\Core\Views;

class AdminSidebar
{
    /**
     * Dynamically registered business module nodes.
     *
     * @var array<int, array<string, mixed>>
     */
    protected static array $businessModules = [];

    /**
     * Allow modules to contribute entries under the business units section.
     */
    public static function registerBusinessModule(array $module): void
    {
        self::$businessModules[] = $module;
    }

    /**
     * Build the navigation structure for the admin sidebar.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function navigation(): array
    {
        $businessItems = [
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
                'route' => 'admin.finance.invoices.index',
                'pattern' => 'admin/finance*',
                'children' => [
                    ['label' => 'Satış Faturaları', 'route' => 'admin.finance.invoices.index', 'pattern' => 'admin/finance/invoices*'],
                    ['label' => 'Tahsilatlar', 'route' => 'admin.finance.receipts.index', 'pattern' => 'admin/finance/receipts*'],
                    ['label' => 'Tahsilat Dağılımı', 'route' => 'admin.finance.allocations.index', 'pattern' => 'admin/finance/allocations*'],
                    ['label' => 'Banka Hesapları', 'route' => 'admin.finance.bank-accounts.index', 'pattern' => 'admin/finance/bank-accounts*'],
                    ['label' => 'Banka Hareketleri', 'route' => 'admin.finance.bank-transactions.index', 'pattern' => 'admin/finance/bank-transactions*'],
                    ['label' => 'Yaşlandırma Raporu', 'route' => 'admin.finance.reports.aging', 'pattern' => 'admin/finance/reports/aging*'],
                    ['label' => 'Tahsilat Raporu', 'route' => 'admin.finance.reports.receipts', 'pattern' => 'admin/finance/reports/receipts*'],
                    ['label' => 'Finansal Özet', 'route' => 'admin.finance.reports.summary', 'pattern' => 'admin/finance/reports/summary*'],
                    ['label' => 'Borç Faturaları', 'route' => 'admin.finance.ap-invoices.index', 'pattern' => 'admin/finance/ap-invoices*'],
                    ['label' => 'Borç Ödemeleri', 'route' => 'admin.finance.ap-payments.index', 'pattern' => 'admin/finance/ap-payments*'],
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
        ];

        if (self::$businessModules) {
            $businessItems = array_merge($businessItems, self::$businessModules);
        }

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
                'items' => $businessItems,
            ],
        ];
    }
}