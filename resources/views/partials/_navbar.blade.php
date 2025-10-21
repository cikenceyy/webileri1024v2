<nav class="navbar navbar-expand-lg bg-white shadow-sm" data-ui="header">
    <div class="container-fluid">
        <button class="btn btn-link px-2" type="button" data-action="toggle" data-target="#sidebar" aria-expanded="true">
            <span class="visually-hidden">Menüyü aç/kapat</span>
            <i class="bi bi-list"></i>
        </button>

        <div class="d-flex align-items-center gap-2">
            <span class="fw-semibold text-primary">{{ config('app.name', 'Webileri') }}</span>
            <span class="text-muted">@yield('section', 'Dashboard')</span>
        </div>

        <div class="ms-auto" role="toolbar" aria-label="Header actions">
            @hasSection('navbar-actions')
                @yield('navbar-actions')
            @else
                <x-ui.toolbar :items="[
                    ['label' => 'Sipariş - Sevkiyat - Fatura', 'icon' => 'bi bi-diagram-3', 'action' => '#'],
                    ['label' => 'Talep -> Satın Alma - Fatura', 'icon' => 'bi bi-bag-check', 'action' => '#'],
                    ['label' => 'İş Emri - Üretim', 'icon' => 'bi bi-cpu', 'action' => '#'],
                ]" />
            @endif
        </div>
    </div>
</nav>
