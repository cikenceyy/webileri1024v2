<aside id="sidebar" class="ui-sidebar" data-ui="sidebar" data-variant="tooltip">
    <div class="ui-sidebar__inner">
        <div class="ui-sidebar__brand">
            <span class="ui-sidebar__logo" aria-hidden="true">#</span>
            <span class="ui-sidebar__product">{{ config('app.name', 'KOBİ Admin') }}</span>
        </div>

        @php
            $dashboardRoute = Route::has('admin.dashboard') ? route('admin.dashboard') : '#';
        @endphp

        <nav class="ui-sidebar__nav" aria-label="Primary navigation">
            <ul class="ui-sidebar__list">
                <li class="ui-sidebar__item @if(request()->routeIs('admin.dashboard')) is-active @endif">
                    <a href="{{ $dashboardRoute }}" class="ui-sidebar__link" aria-current="page" title="Gösterge Paneli">
                        <i class="bi bi-speedometer2 ui-sidebar__icon" aria-hidden="true"></i>
                        <span class="ui-sidebar__label">Gösterge Paneli</span>
                    </a>
                </li>
                <li class="ui-sidebar__item">
                    <a href="#" class="ui-sidebar__link" title="Marketing">
                        <i class="bi bi-people ui-sidebar__icon" aria-hidden="true"></i>
                        <span class="ui-sidebar__label">Marketing</span>
                    </a>
                </li>
                <li class="ui-sidebar__item">
                    <a href="#" class="ui-sidebar__link" title="Stok">
                        <i class="bi bi-box-seam ui-sidebar__icon" aria-hidden="true"></i>
                        <span class="ui-sidebar__label">Stok</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
