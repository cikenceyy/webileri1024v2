{{-- ConsoleKit stok ekranı: hızlı grid, komut çubuğu ve bulk işlemler. --}}
@extends('layouts.admin')

@section('title', 'Stok Konsolu')
@section('module', 'Inventory')

@push('page-styles')
    @vite('app/Modules/Inventory/Resources/scss/console.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Inventory/Resources/js/console.js')
@endpush

@section('content')
    <div class="console-kit"
         data-grid-endpoint="{{ route('admin.inventory.stock.console.grid') }}"
         data-bulk-endpoint="{{ route('admin.inventory.stock.console.store') }}"
         data-bulk-jobs="{{ route('admin.bulk-jobs.index') }}"
         data-poll-interval="{{ $pollInterval }}"
         data-commands='@json($commands)'
         data-quick-filters='@json($quickFilters)'
         data-columns='@json($grid->columns)'>
        <header class="console-kit__header">
            <div>
                <h1 class="console-kit__title">Stok Konsolu</h1>
                <p class="console-kit__subtitle">Depo bazlı stokları tek ekranda izleyin, hızlı komutlarla aksiyon alın.</p>
            </div>
            <div class="console-kit__actions">
                <button type="button" class="btn btn-outline-light" data-console-command>
                    <span class="me-1" aria-hidden="true">⌘</span> Komut Çubuğu (.)
                </button>
                <button type="button" class="btn btn-primary" data-console-refresh title="Grid verisini yenile">
                    Yenile
                </button>
            </div>
        </header>

        <section class="console-kit__filters" aria-label="Hızlı filtreler">
            <div class="console-kit__filter-row">
                <div class="console-kit__quick">
                    @foreach ($quickFilters as $filter)
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                data-filter-id="{{ $filter['id'] }}">
                            {{ $filter['label'] }}
                        </button>
                    @endforeach
                </div>
                <div class="console-kit__search">
                    <input type="search" class="form-control" placeholder="alan:değer veya ürün adı" data-console-search>
                </div>
                <div class="console-kit__warehouse">
                    <label class="form-label mb-1">Depo</label>
                    <select class="form-select form-select-sm" data-console-warehouse>
                        <option value="">Hepsi</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="alert alert-info console-kit__hint" role="note">
                Filtre söz dizimi örnekleri: <code>qty:0..100</code>, <code>warehouse_id:5</code>, <code>updated_at:2024-01-01..2024-02-01</code>
            </div>
        </section>

        <section class="console-kit__body" aria-live="polite">
            <div class="table-responsive console-kit__grid" data-console-grid>
                <table class="table table-hover">
                    <thead>
                        <tr></tr>
                    </thead>
                    <tbody>
                        <tr><td class="text-muted">Veri yükleniyor…</td></tr>
                    </tbody>
                </table>
                <div class="console-kit__pager" data-console-pager></div>
            </div>
            <aside class="console-kit__sidebar" aria-label="Toplu işlemler">
                <h2 class="console-kit__sidebar-title">Toplu İşler</h2>
                <div class="console-kit__jobs" data-console-jobs>
                    <p class="text-muted mb-0">Henüz kuyrukta iş yok.</p>
                </div>
                <div class="console-kit__footer" data-console-status>
                    <span class="badge bg-success">●</span>
                    <span class="ms-2">Son güncelleme: <span data-console-updated>—</span></span>
                    <span class="ms-3">Seçili: <span data-console-selected>0</span></span>
                </div>
            </aside>
        </section>

        <template id="console-command-template">
            <div class="console-command" role="dialog" aria-modal="true" aria-label="Komut Çubuğu">
                <div class="console-command__box">
                    <div class="console-command__header">
                        <input type="search" class="console-command__search" placeholder="Komut ara" />
                        <button type="button" class="btn-close" data-close></button>
                    </div>
                    <div class="console-command__list" role="listbox"></div>
                </div>
            </div>
        </template>
    </div>
@endsection
