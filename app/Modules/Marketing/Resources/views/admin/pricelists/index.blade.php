@extends('layouts.admin')

@section('title', 'Fiyat Listeleri')
@section('module', 'Marketing')

@push('page-styles')
    @vite('app/Modules/Marketing/Resources/scss/pricelists.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Marketing/Resources/js/pricelists.js')
@endpush

@section('content')
    <section class="inv-prices inv-prices--list">
        <header class="inv-prices__hero">
            <div>
                <p class="inv-prices__eyebrow">{{ __('Fiyat Yönetimi') }}</p>
                <h1 class="inv-prices__title">{{ __('Fiyat Listeleri') }}</h1>
                <p class="inv-prices__subtitle">
                    {{ __('Ekibinizin kullandığı tüm fiyat listelerini görüntüleyin, filtreleyin ve en güncel kalanlarla çalışın.') }}
                </p>
            </div>
            <form method="get" class="inv-prices__search" role="search">
                <div class="input-group input-group-lg">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" name="q" class="form-control" placeholder="{{ __('Liste ara…') }}" value="{{ $filters['q'] ?? '' }}">
                    @if (!empty($filters['status']) && $filters['status'] !== 'all')
                        <input type="hidden" name="status" value="{{ $filters['status'] }}">
                    @endif
                </div>
            </form>
        </header>

        <section class="inv-prices__stats" aria-label="{{ __('Fiyat listesi istatistikleri') }}">
            <article class="inv-prices__stat-card">
                <p class="inv-prices__stat-label">{{ __('Toplam Liste') }}</p>
                <p class="inv-prices__stat-value">{{ number_format($stats['total']) }}</p>
                <p class="inv-prices__stat-hint">{{ __('Aktif ve pasif tüm listeler') }}</p>
            </article>
            <article class="inv-prices__stat-card">
                <p class="inv-prices__stat-label">{{ __('Aktif') }}</p>
                <p class="inv-prices__stat-value text-success">{{ number_format($stats['active']) }}</p>
                <p class="inv-prices__stat-hint">{{ __('Satışta kullanılan listeler') }}</p>
            </article>
            <article class="inv-prices__stat-card">
                <p class="inv-prices__stat-label">{{ __('Pasif') }}</p>
                <p class="inv-prices__stat-value text-danger">{{ number_format($stats['inactive']) }}</p>
                <p class="inv-prices__stat-hint">{{ __('Arşivde tutulan listeler') }}</p>
            </article>
            <article class="inv-prices__stat-card">
                <p class="inv-prices__stat-label">{{ __('Ortalama Kalem') }}</p>
                <p class="inv-prices__stat-value">{{ number_format($stats['averageItems'], 1) }}</p>
                <p class="inv-prices__stat-hint">{{ __('Liste başına ortalama ürün sayısı') }}</p>
            </article>
        </section>

        <div class="inv-prices__filters">
            <div class="btn-group" role="group" aria-label="{{ __('Durum filtreleri') }}">
                @php($status = $filters['status'] ?? 'all')
                <a class="btn btn-outline-secondary @if($status === 'all') active @endif" href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => null]) }}">{{ __('Tümü') }}</a>
                <a class="btn btn-outline-secondary @if($status === 'active') active @endif" href="{{ request()->fullUrlWithQuery(['status' => 'active', 'page' => null]) }}">{{ __('Aktif') }}</a>
                <a class="btn btn-outline-secondary @if($status === 'inactive') active @endif" href="{{ request()->fullUrlWithQuery(['status' => 'inactive', 'page' => null]) }}">{{ __('Pasif') }}</a>
            </div>
            <p class="text-muted small mb-0">{{ __('Filtreler listenizi daraltır, sayfa yeniden yüklendiğinde korunur.') }}</p>
        </div>

        <div class="inv-prices__grid">
            @forelse ($priceLists as $list)
                <article class="inv-card" data-pricelist-card>
                    <header class="inv-card__header">
                        <div>
                            <h2 class="inv-card__title">{{ $list->name }}</h2>
                            <p class="inv-card__meta">{{ __(':count kalem • :currency', ['count' => $list->items_count, 'currency' => $list->currency]) }}</p>
                        </div>
                        <span class="inv-badge @if($list->active) inv-badge--success @else inv-badge--muted @endif">
                            {{ $list->active ? __('Aktif') : __('Pasif') }}
                        </span>
                    </header>
                    <div class="inv-card__body">
                        <p class="inv-card__hint">{{ __('Son güncellenen fiyatları inceleyin ve hızlıca harekete geçin.') }}</p>
                    </div>
                    <footer class="inv-card__footer">
                        <a href="{{ route('admin.marketing.pricelists.show', $list) }}" class="btn btn-sm btn-primary">{{ __('Detayları Gör') }}</a>
                        <a href="{{ route('admin.marketing.pricelists.bulk.form', $list) }}" class="btn btn-sm btn-outline-secondary">{{ __('Toplu Güncelle') }}</a>
                    </footer>
                </article>
            @empty
                <div class="inv-prices__empty">
                    <h2 class="h4 mb-2">{{ __('Henüz fiyat listesi yok') }}</h2>
                    <p class="text-muted mb-0">{{ __('Yeni bir liste oluşturmak için envanter ekibinizle iletişime geçebilirsiniz.') }}</p>
                </div>
            @endforelse
        </div>

        <footer class="inv-prices__pagination">
            {{ $priceLists->links() }}
        </footer>
    </section>
@endsection
