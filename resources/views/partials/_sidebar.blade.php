@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;

    $routeName = request()->route()?->getName();

    $navigation = [
        [
            'title' => 'Genel Bakış',
            'description' => 'Günlük ritim ve üst düzey göstergeler',
            'items' => [
                [
                    'label' => 'Gösterge Paneli',
                    'icon' => 'bi bi-speedometer2',
                    'route' => 'admin.dashboard',
                ],
                [
                    'label' => 'KOBİ Konsolu',
                    'icon' => 'bi bi-kanban',
                    'href' => url('admin/console'),
                    'pattern' => 'admin/console*',
                    'children' => [
                        ['label' => 'Genel Akış', 'href' => url('admin/console/activity'), 'pattern' => 'admin/console/activity*'],
                        ['label' => 'Takvim & Ajanda', 'href' => url('admin/console/calendar'), 'pattern' => 'admin/console/calendar*'],
                        ['label' => 'Onay Bekleyenler', 'href' => url('admin/console/approvals'), 'pattern' => 'admin/console/approvals*'],
                    ],
                ],
                [
                    'label' => 'Drive',
                    'icon' => 'bi bi-cloud-arrow-down',
                    'href' => url('admin/drive'),
                    'pattern' => 'admin/drive*',
                    'children' => [
                        ['label' => 'Paylaşılan Belgeler', 'href' => url('admin/drive/shared'), 'pattern' => 'admin/drive/shared*'],
                        ['label' => 'Sözleşmeler', 'href' => url('admin/drive/contracts'), 'pattern' => 'admin/drive/contracts*'],
                        ['label' => 'Şirket Arşivi', 'href' => url('admin/drive/archive'), 'pattern' => 'admin/drive/archive*'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Müşteri & Pazarlama',
            'description' => 'Satış hunisi, kampanyalar ve müşteri iletişimi',
            'items' => [
                [
                    'label' => 'CRM & Satış',
                    'icon' => 'bi bi-people',
                    'prefixes' => ['admin.crmsales.'],
                    'children' => [
                        ['label' => 'Potansiyel Müşteriler', 'route' => 'admin.crmsales.leads', 'pattern' => 'admin/crm/leads*'],
                        ['label' => 'Teklifler', 'route' => 'admin.crmsales.quotes', 'pattern' => 'admin/crm/quotes*'],
                        ['label' => 'Satış Siparişleri', 'route' => 'admin.crmsales.orders', 'pattern' => 'admin/crm/orders*'],
                        ['label' => 'Tahsilatlar', 'route' => 'admin.crmsales.collections', 'pattern' => 'admin/crm/collections*'],
                    ],
                ],
                [
                    'label' => 'Marketing',
                    'icon' => 'bi bi-bullseye',
                    'href' => url('admin/marketing'),
                    'pattern' => 'admin/marketing*',
                    'children' => [
                        ['label' => 'Kampanyalar', 'href' => url('admin/marketing/campaigns'), 'pattern' => 'admin/marketing/campaigns*'],
                        ['label' => 'Segmentler', 'href' => url('admin/marketing/segments'), 'pattern' => 'admin/marketing/segments*'],
                        ['label' => 'İletişim Akışı', 'href' => url('admin/marketing/journeys'), 'pattern' => 'admin/marketing/journeys*'],
                    ],
                ],
                [
                    'label' => 'Destek & Bilet',
                    'icon' => 'bi bi-life-preserver',
                    'href' => url('admin/support'),
                    'pattern' => 'admin/support*',
                    'children' => [
                        ['label' => 'Bilet Kutusu', 'href' => url('admin/support/tickets'), 'pattern' => 'admin/support/tickets*'],
                        ['label' => 'Servis Seviyeleri', 'href' => url('admin/support/sla'), 'pattern' => 'admin/support/sla*'],
                        ['label' => 'Çözüm Merkezi', 'href' => url('admin/support/knowledge'), 'pattern' => 'admin/support/knowledge*'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Operasyon & Kaynaklar',
            'description' => 'Stok, üretim ve tedarik konsolları',
            'items' => [
                [
                    'label' => 'Inventory Console',
                    'icon' => 'bi bi-box-seam',
                    'href' => url('admin/inventory/console'),
                    'pattern' => 'admin/inventory/console*',
                    'children' => [
                        ['label' => 'Envanter Anlık Görünüm', 'href' => url('admin/inventory/console/overview'), 'pattern' => 'admin/inventory/console/overview*'],
                        ['label' => 'Depo Performansı', 'href' => url('admin/inventory/console/warehouses'), 'pattern' => 'admin/inventory/console/warehouses*'],
                        ['label' => 'Sayım Planları', 'href' => url('admin/inventory/console/counts'), 'pattern' => 'admin/inventory/console/counts*'],
                    ],
                ],
                [
                    'label' => 'Stok & Üretim',
                    'icon' => 'bi bi-boxes',
                    'prefixes' => ['admin.inventory.', 'admin.production.'],
                    'children' => [
                        ['label' => 'Ürün Kartları', 'route' => 'admin.inventory.products', 'pattern' => 'admin/inventory/products*'],
                        ['label' => 'Stok Hareketleri', 'route' => 'admin.inventory.movements', 'pattern' => 'admin/inventory/movements*'],
                        ['label' => 'Üretim Emirleri', 'route' => 'admin.production.work-orders', 'pattern' => 'admin/production/work-orders*'],
                        ['label' => 'Kalite Kontrol', 'route' => 'admin.production.quality', 'pattern' => 'admin/production/quality*'],
                    ],
                ],
                [
                    'label' => 'Lojistik & Tedarik',
                    'icon' => 'bi bi-truck',
                    'prefixes' => ['admin.logistics.', 'admin.procurement.'],
                    'children' => [
                        ['label' => 'Sevkiyat Planı', 'route' => 'admin.logistics.shipments', 'pattern' => 'admin/logistics/shipments*'],
                        ['label' => 'Tedarik Talepleri', 'route' => 'admin.procurement.requests', 'pattern' => 'admin/procurement/requests*'],
                        ['label' => 'Tedarikçi Yönetimi', 'route' => 'admin.procurement.suppliers', 'pattern' => 'admin/procurement/suppliers*'],
                    ],
                ],
                [
                    'label' => 'İK & Bordro',
                    'icon' => 'bi bi-person-badge',
                    'href' => url('admin/hr'),
                    'pattern' => 'admin/hr*',
                    'children' => [
                        ['label' => 'Çalışanlar', 'href' => url('admin/hr/employees'), 'pattern' => 'admin/hr/employees*'],
                        ['label' => 'Vardiyalar', 'href' => url('admin/hr/shifts'), 'pattern' => 'admin/hr/shifts*'],
                        ['label' => 'Bordrolar', 'href' => url('admin/hr/payroll'), 'pattern' => 'admin/hr/payroll*'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Finans & Analitik',
            'description' => 'Karar destek ve finansal denetim',
            'items' => [
                [
                    'label' => 'Finans & Muhasebe',
                    'icon' => 'bi bi-cash-coin',
                    'prefixes' => ['admin.finance.'],
                    'children' => [
                        ['label' => 'Faturalar', 'route' => 'admin.finance.invoices', 'pattern' => 'admin/finance/invoices*'],
                        ['label' => 'Ödemeler', 'route' => 'admin.finance.payments', 'pattern' => 'admin/finance/payments*'],
                        ['label' => 'Bütçe Yönetimi', 'route' => 'admin.finance.budget', 'pattern' => 'admin/finance/budget*'],
                    ],
                ],
                [
                    'label' => 'Analitik & Raporlar',
                    'icon' => 'bi bi-graph-up-arrow',
                    'pattern' => 'admin/reports*',
                    'children' => [
                        ['label' => 'Satış Raporları', 'route' => 'admin.reports.sales', 'pattern' => 'admin/reports/sales*'],
                        ['label' => 'Finansal Özet', 'route' => 'admin.reports.finance', 'pattern' => 'admin/reports/finance*'],
                        ['label' => 'Operasyon Takibi', 'route' => 'admin.reports.operations', 'pattern' => 'admin/reports/operations*'],
                    ],
                ],
                [
                    'label' => 'Strateji Panoları',
                    'icon' => 'bi bi-compass',
                    'href' => url('admin/strategy'),
                    'pattern' => 'admin/strategy*',
                    'children' => [
                        ['label' => 'OKR Takibi', 'href' => url('admin/strategy/okrs'), 'pattern' => 'admin/strategy/okrs*'],
                        ['label' => 'Yönetim Raporları', 'href' => url('admin/strategy/executive'), 'pattern' => 'admin/strategy/executive*'],
                        ['label' => 'Risk İzleme', 'href' => url('admin/strategy/risks'), 'pattern' => 'admin/strategy/risks*'],
                    ],
                ],
            ],
        ],
    ];

    $makeUrl = static function (array $item): string {
        if (isset($item['href'])) {
            return $item['href'];
        }

        if (isset($item['route']) && Route::has($item['route'])) {
            return route($item['route']);
        }

        if (isset($item['route'])) {
            $path = str_replace('.', '/', $item['route']);
            return url($path);
        }

        if (isset($item['pattern'])) {
            $pattern = trim($item['pattern'], '*');
            return url($pattern);
        }

        return '#';
    };

    $matchesPattern = static function ($patterns): bool {
        foreach (Arr::wrap($patterns) as $pattern) {
            if (request()->is($pattern)) {
                return true;
            }
        }

        return false;
    };

    $isItemActive = static function (array $item) use ($routeName, $matchesPattern): bool {
        if (isset($item['active'])) {
            return (bool) $item['active'];
        }

        if (isset($item['route']) && $routeName === $item['route']) {
            return true;
        }

        if (!empty($item['prefixes']) && $routeName) {
            foreach ($item['prefixes'] as $prefix) {
                if (str_starts_with($routeName, $prefix)) {
                    return true;
                }
            }
        }

        if (!empty($item['pattern']) && $matchesPattern($item['pattern'])) {
            return true;
        }

        return false;
    };

    $isSectionOpen = static function (array $item) use ($isItemActive): bool {
        if (empty($item['children'])) {
            return $isItemActive($item);
        }

        foreach ($item['children'] as $child) {
            if ($isItemActive($child)) {
                return true;
            }
        }

        return $isItemActive($item);
    };
@endphp

<aside id="sidebar" class="ui-sidebar" data-ui="sidebar" data-variant="tooltip">
    <div class="ui-sidebar__inner">
        <nav class="ui-sidebar__nav" aria-label="Birincil gezinme">
            @foreach($navigation as $sectionIndex => $section)
                <section class="ui-sidebar__section">
                    <header class="ui-sidebar__section-header">
                        <span class="ui-sidebar__section-title">{{ $section['title'] }}</span>
                        @if(!empty($section['description']))
                            <span class="ui-sidebar__section-description">{{ $section['description'] }}</span>
                        @endif
                    </header>

                    <ul class="ui-sidebar__list">
                        @foreach($section['items'] as $itemIndex => $item)
                            @php
                                $isOpen = $isSectionOpen($item);
                                $hasChildren = !empty($item['children']);
                                $itemUrl = $makeUrl($item);
                                $collapseId = sprintf(
                                    'sidebar-node-%d-%d-%s',
                                    $sectionIndex,
                                    $itemIndex,
                                    Str::slug($item['label']) ?: 'item'
                                );
                            @endphp

                            <li
                                class="ui-sidebar__item {{ $isOpen ? 'is-open' : '' }} {{ $hasChildren ? 'has-children' : '' }}"
                                @if($hasChildren)
                                    data-sidebar-collapsible
                                    data-sidebar-id="{{ $collapseId }}"
                                @endif
                            >
                                @if($hasChildren)
                                    <button
                                        class="ui-sidebar__trigger"
                                        type="button"
                                        data-role="sidebar-trigger"
                                        id="{{ $collapseId }}-trigger"
                                        aria-controls="{{ $collapseId }}-panel"
                                        aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                                        aria-label="{{ $item['label'] }}"
                                    >
                                        <span class="ui-sidebar__icon" aria-hidden="true"><i class="{{ $item['icon'] ?? 'bi bi-circle' }}"></i></span>
                                        <span class="ui-sidebar__label">{{ $item['label'] }}</span>
                                        <span class="ui-sidebar__caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                                    </button>

                                    <div
                                        class="ui-sidebar__panel"
                                        id="{{ $collapseId }}-panel"
                                        data-role="sidebar-panel"
                                        role="region"
                                        aria-labelledby="{{ $collapseId }}-trigger"
                                        aria-hidden="{{ $isOpen ? 'false' : 'true' }}"
                                        @unless($isOpen) hidden @endunless
                                    >
                                        <ul class="ui-sidebar__sublist">
                                            @foreach($item['children'] as $child)
                                                @php
                                                    $childUrl = $makeUrl($child);
                                                    $childActive = $isItemActive($child);
                                                @endphp
                                                <li class="ui-sidebar__subitem {{ $childActive ? 'is-active' : '' }}">
                                                    <a href="{{ $childUrl }}" class="ui-sidebar__sublink">
                                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                                        <span class="ui-sidebar__sublabel">{{ $child['label'] }}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <a href="{{ $itemUrl }}" class="ui-sidebar__link" aria-label="{{ $item['label'] }}" @if($isOpen) aria-current="page" @endif>
                                        <span class="ui-sidebar__icon" aria-hidden="true"><i class="{{ $item['icon'] ?? 'bi bi-circle' }}"></i></span>
                                        <span class="ui-sidebar__label">{{ $item['label'] }}</span>
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </nav>
    </div>

    <footer class="ui-sidebar__footer" aria-label="Destek bağlantıları">
        <div class="ui-sidebar__footer-card">
            <span class="ui-sidebar__footer-eyebrow">Yardıma mı ihtiyacınız var?</span>
            <p class="ui-sidebar__footer-text">Operasyon ekibimiz size destek olmak için hazır. Destek merkezinden yeni bir talep oluşturabilirsiniz.</p>
            <a class="ui-sidebar__footer-link" href="{{ url('admin/support/new-ticket') }}">
                <i class="bi bi-chat-dots"></i>
                <span>Destek Talebi Oluştur</span>
            </a>
        </div>
    </footer>
</aside>
