<header class="ui-header" data-ui="header">
    <div class="ui-header__inner">
        <button
            class="ui-header__toggle"
            type="button"
            data-action="toggle"
            data-target="#sidebar"
            aria-controls="sidebar"
            aria-expanded="true"
        >
            <span class="ui-header__toggle-icon" aria-hidden="true">
                <i class="bi bi-list"></i>
            </span>
            <span class="visually-hidden">Menüyü aç/kapat</span>
        </button>

        <div class="ui-header__title">
            <span class="ui-header__product">{{ config('app.name', 'Webileri') }}</span>
            <span class="ui-header__section">@yield('section', 'Gösterge Paneli')</span>
        </div>

        <nav class="ui-header__nav" aria-label="Üst bağlantılar">
            <a href="{{ url('admin/console') }}" class="ui-header__nav-link">Console</a>
            <a href="{{ url('admin/activity') }}" class="ui-header__nav-link">Akış</a>
        </nav>

        <div class="ui-header__actions" role="toolbar" aria-label="Header actions">
            @hasSection('navbar-actions')
                @yield('navbar-actions')
            @else
                <x-ui-toolbar :items="[
                    ['label' => 'Sipariş · Sevkiyat', 'icon' => 'bi bi-diagram-3', 'action' => url('admin/operations/shipments')],
                    ['label' => 'Talep · Satın Alma', 'icon' => 'bi bi-bag-check', 'action' => url('admin/procurement/requests')],
                    ['label' => 'İş Emri · Üretim', 'icon' => 'bi bi-cpu', 'action' => url('admin/manufacturing/work-orders')],
                ]" />
            @endif

            @auth
                <form method="POST" action="{{ route('admin.auth.logout') }}" class="ui-header__logout">
                    @csrf
                    <x-ui-button type="submit" variant="ghost" size="sm" icon="bi bi-box-arrow-right">{{ __('Çıkış Yap') }}</x-ui-button>
                </form>
            @endauth
        </div>
    </div>
</header>
