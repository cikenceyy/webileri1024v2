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
                    <a href="/admin/console" class="ui-header__nav-link">
                        <span class="ui-header__nav-icon" aria-hidden="true"><i class="bi bi-kanban"></i></span>
                        <span class="ui-header__nav-text">Konsol</span>
                    </a>
                </li>
                <li class="ui-header__nav-item">
                    <a href="/admin/activity" class="ui-header__nav-link">
                        <span class="ui-header__nav-icon" aria-hidden="true"><i class="bi bi-lightning-charge"></i></span>
                        <span class="ui-header__nav-text">Akış</span>
                    </a>
                </li>
                <li class="ui-header__nav-item">
                    <a href="/admin/marketing" class="ui-header__nav-link">
                        <span class="ui-header__nav-icon" aria-hidden="true"><i class="bi bi-bullseye"></i></span>
                        <span class="ui-header__nav-text">Pazarlama</span>
                    </a>
                </li>
                <li class="ui-header__nav-item">
                    <a href="/admin/inventory/console" class="ui-header__nav-link">
                        <span class="ui-header__nav-icon" aria-hidden="true"><i class="bi bi-boxes"></i></span>
                        <span class="ui-header__nav-text">Envanter</span>
                    </a>
                </li>
                <li class="ui-header__nav-item">
                    <a href="/admin/drive" class="ui-header__nav-link">
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

                <form method="POST" action="/admin/auth/logout" class="ui-header__logout">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button type="submit" class="ui-header__action is-ghost">
                        <span class="ui-header__action-icon" aria-hidden="true"><i class="bi bi-box-arrow-right"></i></span>
                        <span class="ui-header__action-label">Çıkış Yap</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
