@extends('layouts.admin')

@section('title', 'Envanter Kontrol Kulesi')
@section('module', 'Inventory')

@push('page-styles')
    @vite('app/Modules/Inventory/Resources/scss/home.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Inventory/Resources/js/home.js')
@endpush

@section('content')
    <div class="inv-home" data-module="inventory-home">
        <section class="inv-home__kpis" data-kpi-region>
            @foreach ($kpis as $kpi)
                <article class="inv-card inv-card--kpi" role="status">
                    <div class="inv-card__meta">
                        <span class="inv-card__icon"><i class="bi {{ $kpi['icon'] ?? 'bi-circle' }}"></i></span>
                        <span class="inv-card__label">{{ $kpi['label'] }}</span>
                    </div>
                    <p class="inv-card__value">{{ $kpi['value'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="inv-home__quickbar" aria-label="Hızlı aksiyonlar">
            @foreach ($quickActions as $action)
                <a href="{{ $action['route'] }}"
                   class="inv-home__quick-action"
                   data-action="inventory-quick"
                   data-mode="{{ $action['mode'] }}">
                    <span class="inv-home__quick-icon"><i class="bi {{ $action['icon'] ?? 'bi-lightning' }}"></i></span>
                    <span class="inv-home__quick-label">{{ $action['label'] }}</span>
                </a>
            @endforeach
        </section>

        <section class="inv-home__timeline" data-timeline-region aria-label="Bugün hareketleri">
            <header class="inv-home__section-header">
                <h2 class="inv-home__section-title">Bugün Hareketler</h2>
            </header>
            <ol class="inv-timeline">
                @forelse ($timeline as $event)
                    <li class="inv-timeline__item">
                        <div class="inv-timeline__time">{{ $event['timestamp'] }}</div>
                        <div class="inv-timeline__body">
                            <h3 class="inv-timeline__title">{{ $event['title'] }}</h3>
                            <p class="inv-timeline__subtitle">{{ $event['subtitle'] }}</p>
                            @if (!empty($event['link']))
                                <a href="{{ $event['link'] }}" class="inv-timeline__link">Detayı aç</a>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="inv-timeline__item inv-timeline__item--empty">
                        <p>Bugün için kayıtlı hareket bulunmuyor.</p>
                    </li>
                @endforelse
            </ol>
        </section>

        <section class="inv-home__lowstock" data-lowstock-region aria-label="Düşük stok uyarıları">
            <header class="inv-home__section-header">
                <h2 class="inv-home__section-title">Düşük Stoklar</h2>
            </header>
            <div class="inv-home__lowstock-grid">
                @forelse ($lowStock as $item)
                    <article class="inv-card inv-card--lowstock"
                             data-action="inventory-lowstock"
                             data-product-id="{{ $item['id'] }}">
                        <header class="inv-card__header">
                            <span class="inv-card__title">{{ $item['product']->name ?? 'Ürün' }}</span>
                            <span class="inv-card__subtitle">{{ $item['warehouse']->name ?? 'Depo' }}</span>
                        </header>
                        <dl class="inv-card__stats">
                            <div class="inv-card__stat">
                                <dt>SKU</dt>
                                <dd>{{ $item['sku'] }}</dd>
                            </div>
                            <div class="inv-card__stat">
                                <dt>Stok</dt>
                                <dd>{{ number_format($item['qty'], 2) }}</dd>
                            </div>
                            <div class="inv-card__stat">
                                <dt>Hedef</dt>
                                <dd>{{ number_format($item['threshold'], 2) }}</dd>
                            </div>
                        </dl>
                        <footer class="inv-card__footer">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-action="inventory-lowstock-transfer">Transfer öner</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-action="inventory-lowstock-procure">Tedarik planla</button>
                        </footer>
                    </article>
                @empty
                    <p class="text-muted">Düşük stok uyarısı yok.</p>
                @endforelse
            </div>

            <div class="inv-sheet" data-sheet="lowstock" role="dialog" aria-modal="true">
                <div class="inv-sheet__header">
                    <h3 class="inv-sheet__title">Stok Tamamlama Önerisi</h3>
                    <button type="button" class="btn-close" data-action="sheet-dismiss" aria-label="Kapat"></button>
                </div>
                <div class="inv-sheet__body" data-sheet-body>
                    <p class="inv-sheet__hint">Seçili ürün için hedef depo ve önerilen miktarı belirleyin.</p>
                    <form class="inv-sheet__form">
                        <div class="mb-3">
                            <label for="lowstock-target" class="form-label">Hedef depo</label>
                            <select id="lowstock-target" class="form-select">
                                <option value="">Depo seçin</option>
                                @foreach ($quickActions as $action)
                                    <option value="{{ $action['mode'] }}">{{ $action['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="lowstock-qty" class="form-label">Önerilen miktar</label>
                            <input type="number" id="lowstock-qty" class="form-control" min="0" step="0.01" value="0">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary">Onayla</button>
                            <button type="button" class="btn btn-outline-secondary" data-action="sheet-dismiss">Vazgeç</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
