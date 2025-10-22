@extends('layouts.admin')

@section('title', $product->name)
@section('module', 'Inventory')

@push('page-styles')
    @vite('app/Modules/Inventory/Resources/scss/products_show.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Inventory/Resources/js/products_show.js')
@endpush

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    <article class="inv-product" data-product="{{ $product->id }}">
        <header class="inv-product__header">
            <div class="inv-product__gallery">
                @if ($product->media)
                    @php
                        $disk = $product->media->disk ?? config('filesystems.default');
                        $path = $product->media->thumb_path ?: $product->media->path;
                        $imageUrl = $path ? Storage::disk($disk)->url($path) : null;
                    @endphp
                    @if ($imageUrl)
                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="inv-product__image">
                    @else
                        <div class="inv-product__placeholder">Görsel yok</div>
                    @endif
                @else
                    <div class="inv-product__placeholder">Görsel yok</div>
                @endif
            </div>
            <div class="inv-product__identity">
                <h1 class="inv-product__title">{{ $product->name }}</h1>
                <p class="inv-product__meta">SKU: {{ $product->sku }} • Barkod: {{ $product->barcode ?? '—' }}</p>
                <div class="inv-product__variants" data-variant-region>
                    @foreach ($product->variants as $variant)
                        <button type="button" class="inv-product__variant" data-variant-id="{{ $variant->id }}">
                            {{ $variant->sku ?? $variant->id }}
                        </button>
                    @endforeach
                </div>
            </div>
        </header>

        <section class="inv-product__matrix" aria-label="Depo stok matrisi">
            <header class="inv-product__section-header">
                <h2 class="inv-product__section-title">Depo Dağılımı</h2>
            </header>
            <div class="inv-matrix__grid" data-matrix>
                @foreach ($stockByWarehouse as $item)
                    <div class="inv-matrix__cell" data-warehouse="{{ $item->warehouse?->id }}">
                        <span class="inv-matrix__warehouse">{{ $item->warehouse?->name ?? 'Depo' }}</span>
                        <span class="inv-matrix__qty">{{ number_format($item->qty, 2) }}</span>
                    </div>
                @endforeach
            </div>
            <dl class="inv-product__matrix-stats">
                <div>
                    <dt>Toplam Stok</dt>
                    <dd>{{ number_format($onHandTotal, 2) }}</dd>
                </div>
                <div>
                    <dt>Min. Stok</dt>
                    <dd>{{ number_format($reorderPoint, 2) }}</dd>
                </div>
                <div>
                    <dt>Tahmini Tükenme</dt>
                    <dd>{{ $depletionDays ? $depletionDays . ' gün' : '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="inv-product__prices" aria-label="Fiyat listeleri">
            <header class="inv-product__section-header">
                <h2 class="inv-product__section-title">Fiyat Rozetleri</h2>
            </header>
            <div class="inv-product__price-badges">
                @foreach ($priceLists as $list)
                    @php
                        $item = $list->items->first();
                    @endphp
                    <span class="inv-badge">{{ $list->name }} • {{ number_format($item?->price ?? 0, 2) }} {{ $list->currency }}</span>
                @endforeach
            </div>
        </section>

        <section class="inv-product__timeline" aria-label="Son hareketler">
            <header class="inv-product__section-header">
                <h2 class="inv-product__section-title">Son 5 Hareket</h2>
            </header>
            <ol class="inv-timeline">
                @forelse ($recentMovements as $movement)
                    <li class="inv-timeline__item">
                        <div class="inv-timeline__time">{{ optional($movement->moved_at)->format('d.m H:i') }}</div>
                        <div class="inv-timeline__body">
                            <h3 class="inv-timeline__title">{{ strtoupper($movement->direction) }} • {{ $movement->warehouse?->name }}</h3>
                            <p class="inv-timeline__subtitle">{{ number_format($movement->qty, 2) }} {{ $product->unit ?? 'adet' }}</p>
                        </div>
                    </li>
                @empty
                    <li class="inv-timeline__item inv-timeline__item--empty">Henüz hareket bulunmuyor.</li>
                @endforelse
            </ol>
        </section>

        <section class="inv-product__docs" aria-label="Belgeler ve bağlantılar">
            <header class="inv-product__section-header">
                <h2 class="inv-product__section-title">Belgeler</h2>
            </header>
            <div class="inv-product__doc-links">
                <a href="{{ route('admin.inventory.bom.show', $product) }}" class="btn btn-outline-primary">Reçete (BOM)</a>
                <a href="{{ route('admin.inventory.products.components', $product) }}" class="btn btn-outline-secondary">Kullanılan Malzemeler</a>
            </div>
        </section>
    </article>
@endsection
