{{--
    Amaç: Yönetici kenar menüsünde TR dil birliğini ve tutarlı etiketleri sağlamak.
    İlişkiler: PROMPT-1 — TR Dil Birliği.
    Notlar: Rozet açıklamaları TR olarak güncellendi.
--}}
@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;
    use App\Core\Cache\InvalidationService;
    use App\Core\Cache\Keys;
    use App\Core\Views\AdminSidebar;

    $routeName = request()->route()?->getName();

    $companyId = currentCompanyId() ?? 0;
    /** @var InvalidationService $cache */
    $cache = app(InvalidationService::class);
    $sidebarKey = Keys::forTenant($companyId, ['sidebar', 'navigation'], 'v1');
    $sidebarTtl = (int) config('cache.ttl_profiles.warm', 900);
    $navigation = $cache->rememberWithTags(
        $sidebarKey,
        [sprintf('tenant:%d', $companyId), 'sidebar', 'menu'],
        $sidebarTtl,
        static fn () => AdminSidebar::navigation(),
    );

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
                                $itemBadge = 0;
                                if (!empty($item['badge_key']) && isset(${$item['badge_key']})) {
                                    $itemBadge = (int) ${$item['badge_key']};
                                }
                            @endphp

                            <li
                                class="ui-sidebar__item {{ $isOpen ? 'is-open' : '' }} {{ $hasChildren ? 'has-children' : '' }}"
                                @if($hasChildren)
                                    data-sidebar-collapsible="true"
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
                                        data-sidebar-target="{{ $collapseId }}-panel"
                                    >
                                        <span class="ui-sidebar__icon" aria-hidden="true"><i class="{{ $item['icon'] ?? 'bi bi-circle' }}"></i></span>
                                        <span class="ui-sidebar__label">{{ $item['label'] }}</span>
                                        @if($itemBadge > 0)
                                            <span class="ui-sidebar__badge" aria-label="{{ $itemBadge }} okunmamış">{{ $itemBadge }}</span>
                                        @endif
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
                                                    $childBadge = 0;
                                                    if (!empty($child['badge_key']) && isset(${$child['badge_key']})) {
                                                        $childBadge = (int) ${$child['badge_key']};
                                                    }
                                                @endphp
                                                <li class="ui-sidebar__subitem {{ $childActive ? 'is-active' : '' }}">
                                                    <a href="{{ $childUrl }}" class="ui-sidebar__sublink">
                                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                                        <span class="ui-sidebar__sublabel">{{ $child['label'] }}</span>
                                                        @if($childBadge > 0)
                                                            <span class="ui-sidebar__badge" aria-label="{{ $childBadge }} okunmamış">{{ $childBadge }}</span>
                                                        @endif
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    @php
                                        $childBadge = $itemBadge;
                                    @endphp
                                    <a href="{{ $itemUrl }}" class="ui-sidebar__link" aria-label="{{ $item['label'] }}" @if($isOpen) aria-current="page" @endif>
                                        <span class="ui-sidebar__icon" aria-hidden="true"><i class="{{ $item['icon'] ?? 'bi bi-circle' }}"></i></span>
                                        <span class="ui-sidebar__label">{{ $item['label'] }}</span>
                                        @if($childBadge > 0)
                                            <span class="ui-sidebar__badge" aria-label="{{ $childBadge }} okunmamış">{{ $childBadge }}</span>
                                        @endif
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