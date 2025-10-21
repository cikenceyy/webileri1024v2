@php
    use Illuminate\Support\Arr;

    $routeName = request()->route()?->getName();

    $navigation = [
        [
            'label' => 'Gösterge Paneli',
            'icon' => 'bi bi-speedometer2',
            'route' => 'admin.dashboard',
        ],
        [
            'label' => 'Satış & CRM',
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
            'label' => 'Analitik & Raporlar',
            'icon' => 'bi bi-graph-up-arrow',
            'pattern' => 'admin/reports*',
            'children' => [
                ['label' => 'Satış Raporları', 'route' => 'admin.reports.sales', 'pattern' => 'admin/reports/sales*'],
                ['label' => 'Finansal Özet', 'route' => 'admin.reports.finance', 'pattern' => 'admin/reports/finance*'],
                ['label' => 'Operasyon Takibi', 'route' => 'admin.reports.operations', 'pattern' => 'admin/reports/operations*'],
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
        <div class="ui-sidebar__brand">
            <span class="ui-sidebar__logo" aria-hidden="true">#</span>
            <span class="ui-sidebar__product">{{ config('app.name', 'KOBİ Admin') }}</span>
        </div>

        <nav class="ui-sidebar__nav" aria-label="Birincil gezinme">
            <ul class="ui-sidebar__list">
                @foreach($navigation as $item)
                    @php
                        $isOpen = $isSectionOpen($item);
                        $hasChildren = !empty($item['children']);
                        $itemUrl = $makeUrl($item);
                    @endphp

                    <li class="ui-sidebar__item {{ $isOpen ? 'is-open' : '' }} {{ $hasChildren ? 'has-children' : '' }}" @if($hasChildren) data-sidebar-collapsible @endif>
                        @if($hasChildren)
                            <button
                                class="ui-sidebar__trigger"
                                type="button"
                                data-role="sidebar-trigger"
                                aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                                aria-label="{{ $item['label'] }}"
                            >
                                <span class="ui-sidebar__icon" aria-hidden="true"><i class="{{ $item['icon'] ?? 'bi bi-circle' }}"></i></span>
                                <span class="ui-sidebar__label">{{ $item['label'] }}</span>
                                <span class="ui-sidebar__caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                            </button>

                            <div class="ui-sidebar__panel" data-role="sidebar-panel" @unless($isOpen) hidden @endunless>
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
        </nav>
    </div>
</aside>
