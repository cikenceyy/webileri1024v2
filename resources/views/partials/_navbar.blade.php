<header class="ui-header" data-ui="header">
    <div class="ui-header__inner">
        <button
            class="ui-header__toggle"
            type="button"
            data-action="toggle"
            data-target="#sidebar"
            aria-controls="sidebar"
            aria-expanded="false"
        >
            <span class="ui-header__toggle-icon" aria-hidden="true">
                <i class="bi bi-list"></i>
            </span>
            <span class="visually-hidden">Menüyü aç/kapat</span>
        </button>

        <div class="ui-header__title">
            <span class="ui-header__product">Webileri</span>
            <span class="ui-header__section">@yield('section', 'Gösterge Paneli')</span>
        </div>

        @hasSection('breadcrumbs')
            <nav class="ui-header__breadcrumbs" aria-label="Gezinme izi">
                @yield('breadcrumbs')
            </nav>
        @endif

        <nav class="ui-header__nav" aria-label="Modül kısayolları">
            <ul class="ui-header__nav-list">
                <li class="ui-header__nav-item">
                    @php $isConsoleActive = request()->routeIs('admin.consoles.*'); @endphp
                    <a
                        href="{{ route('admin.consoles.o2c.index') }}"
                        class="ui-header__nav-link{{ $isConsoleActive ? ' is-active' : '' }}"
                        @if($isConsoleActive) aria-current="page" @endif
                    >
                        <span class="ui-header__nav-icon" aria-hidden="true"><i class="bi bi-kanban"></i></span>
                        <span class="ui-header__nav-text">Konsol</span>
                    </a>
                </li>
                <li class="ui-header__nav-item">
                    @php $isCustomersActive = request()->routeIs('admin.marketing.customers.*'); @endphp
                    <a
                        href="{{ route('admin.marketing.customers.index') }}"
                        class="ui-header__nav-link{{ $isCustomersActive ? ' is-active' : '' }}"
                        @if($isCustomersActive) aria-current="page" @endif
                    >
                        <span class="ui-header__nav-icon" aria-hidden="true"><i class="bi bi-people"></i></span>
                        <span class="ui-header__nav-text">Müşteriler</span>
                    </a>
                </li>
                <li class="ui-header__nav-item">
                    @php $isOrdersActive = request()->routeIs('admin.marketing.orders.*'); @endphp
                    <a
                        href="{{ route('admin.marketing.orders.index') }}"
                        class="ui-header__nav-link{{ $isOrdersActive ? ' is-active' : '' }}"
                        @if($isOrdersActive) aria-current="page" @endif
                    >
                        <span class="ui-header__nav-icon" aria-hidden="true"><i class="bi bi-receipt-cutoff"></i></span>
                        <span class="ui-header__nav-text">Siparişler</span>
                    </a>
                </li>
                <li class="ui-header__nav-item">
                    @php $isInventoryActive = request()->routeIs('admin.inventory.*'); @endphp
                    <a
                        href="{{ route('admin.inventory.home') }}"
                        class="ui-header__nav-link{{ $isInventoryActive ? ' is-active' : '' }}"
                        @if($isInventoryActive) aria-current="page" @endif
                    >
                        <span class="ui-header__nav-icon" aria-hidden="true"><i class="bi bi-box-seam"></i></span>
                        <span class="ui-header__nav-text">Envanter</span>
                    </a>
                </li>
                <li class="ui-header__nav-item">
                    @php $isInvoicesActive = request()->routeIs('admin.finance.invoices.*'); @endphp
                    <a
                        href="{{ route('admin.finance.invoices.index') }}"
                        class="ui-header__nav-link{{ $isInvoicesActive ? ' is-active' : '' }}"
                        @if($isInvoicesActive) aria-current="page" @endif
                    >
                        <span class="ui-header__nav-icon" aria-hidden="true"><i class="bi bi-cash-stack"></i></span>
                        <span class="ui-header__nav-text">Faturalar</span>
                    </a>
                </li>
                <li class="ui-header__nav-item">
                    @php $isDriveActive = request()->routeIs('admin.drive.media.*'); @endphp
                    <a
                        href="{{ route('admin.drive.media.index') }}"
                        class="ui-header__nav-link{{ $isDriveActive ? ' is-active' : '' }}"
                        @if($isDriveActive) aria-current="page" @endif
                    >
                        <span class="ui-header__nav-icon" aria-hidden="true"><i class="bi bi-cloud-arrow-down"></i></span>
                        <span class="ui-header__nav-text">Drive</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="ui-header__actions" role="toolbar" aria-label="Üst çubuk işlemleri">
            <div class="ui-header__action-group" role="group" aria-label="Kullanıcı işlemleri">
                <a href="/admin/console/notifications" class="ui-header__action is-ghost" aria-label="Bildirimler">
                    <span class="ui-header__action-icon" aria-hidden="true"><i class="bi bi-bell"></i></span>
                    <span class="ui-header__action-label">Bildirimler</span>
                </a>

                <a href="/admin/profile" class="ui-header__action is-ghost" aria-label="Profil">
                    <span class="ui-header__action-icon" aria-hidden="true"><i class="bi bi-person-circle"></i></span>
                    <span class="ui-header__action-label">Profil</span>
                </a>

                <form method="POST" action="{{ route('admin.auth.logout') }}" class="ui-header__logout">
                    @csrf
                    <button type="submit" class="ui-header__action is-ghost">
                        <span class="ui-header__action-icon" aria-hidden="true"><i class="bi bi-box-arrow-right"></i></span>
                        <span class="ui-header__action-label">Çıkış Yap</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
