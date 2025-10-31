{{--
    Amaç: Üst gezinme çubuğunu TR dilinde, erişilebilir ve tutarlı davranışla sunmak.
    İlişkiler: PROMPT-5 — Sidebar & Header İyileştirmeleri.
    Notlar: Breadcrumb ve aktiflik göstergesi ortak şablonla yönetilir.
--}}
@php
    $moduleLinks = [
        ['label' => 'Konsol', 'icon' => 'bi bi-kanban', 'href' => url('admin/console'), 'pattern' => 'admin/console*'],
        ['label' => 'Akış', 'icon' => 'bi bi-lightning-charge', 'href' => url('admin/activity'), 'pattern' => 'admin/activity*'],
        ['label' => 'Pazarlama', 'icon' => 'bi bi-bullseye', 'href' => url('admin/marketing'), 'pattern' => 'admin/marketing*'],
        ['label' => 'Envanter', 'icon' => 'bi bi-boxes', 'href' => url('admin/inventory/console'), 'pattern' => 'admin/inventory*'],
        ['label' => 'Drive', 'icon' => 'bi bi-cloud-arrow-down', 'href' => url('admin/drive'), 'pattern' => 'admin/drive*'],
    ];

    $breadcrumbs = trim($__env->yieldContent('breadcrumbs', ''));
@endphp

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
            <span class="ui-header__product">{{ config('app.name', 'Webileri') }}</span>
            <span class="ui-header__section">@yield('section', 'Gösterge Paneli')</span>
        </div>

        @if($breadcrumbs !== '')
            <nav class="ui-header__breadcrumbs" aria-label="Gezinme izi">
                {!! $breadcrumbs !!}
            </nav>
        @endif

        <nav class="ui-header__nav" aria-label="Modül kısayolları">
            <ul class="ui-header__nav-list">
                @foreach($moduleLinks as $link)
                    @php
                        $isActive = !empty($link['pattern']) ? request()->is($link['pattern']) : false;
                    @endphp
                    <li class="ui-header__nav-item">
                        <a href="{{ $link['href'] }}" class="ui-header__nav-link {{ $isActive ? 'is-active' : '' }}">
                            <span class="ui-header__nav-icon" aria-hidden="true"><i class="{{ $link['icon'] }}"></i></span>
                            <span class="ui-header__nav-text">{{ $link['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="ui-header__actions" role="toolbar" aria-label="Üst çubuk işlemleri">
            <div class="ui-header__action-group" role="group" aria-label="Kullanıcı işlemleri">
                <a href="{{ url('admin/console/notifications') }}" class="ui-header__action is-ghost" aria-label="Bildirimler">
                    <span class="ui-header__action-icon" aria-hidden="true"><i class="bi bi-bell"></i></span>
                    <span class="ui-header__action-label">Bildirimler</span>
                </a>

                <a href="{{ url('admin/profile') }}" class="ui-header__action is-ghost" aria-label="Profil">
                    <span class="ui-header__action-icon" aria-hidden="true"><i class="bi bi-person-circle"></i></span>
                    <span class="ui-header__action-label">Profil</span>
                </a>

                @auth
                    <form method="POST" action="{{ route('admin.auth.logout') }}" class="ui-header__logout">
                        @csrf
                        <button type="submit" class="ui-header__action is-ghost">
                            <span class="ui-header__action-icon" aria-hidden="true"><i class="bi bi-box-arrow-right"></i></span>
                            <span class="ui-header__action-label">Çıkış Yap</span>
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
</header>
