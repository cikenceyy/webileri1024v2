@extends('site.layout', ['pageId' => 'catalogs', 'locale' => $locale ?? app()->getLocale()])

@push('site-styles')
    @vite('resources/scss/site/catalogs.scss')
@endpush

@push('site-scripts')
    @vite('resources/js/site/catalogs.js')
@endpush

@section('content')
    @php
        $hero = data_get($data, 'blocks.hero', []);
        $catalogs = data_get($data, 'blocks.list', []);
        $placeholder = fn ($label, $width, $height) => 'data:image/svg+xml;utf8,' . rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$width} {$height}'><rect width='100%' height='100%' fill='%23e4e7ec'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='Inter, sans-serif' font-size='24' fill='%23667085'>{$label}</text></svg>");
    @endphp
    <section class="pattern-hero catalogs-hero" data-module="reveal">
        <div class="stack-lg">
            <h1>{{ $hero['title'] ?? ($locale === 'en' ? 'Catalog library' : 'Katalog kütüphanesi') }}</h1>
            <p class="lead">{{ $hero['subtitle'] ?? ($locale === 'en' ? 'Download brochures and technical documents.' : 'Broşürleri ve teknik dokümanları indirin.') }}</p>
        </div>
    </section>

    <section class="pattern-catalog-grid" data-module="reveal">
        <div class="catalog-grid grid-auto">
            @forelse($catalogs as $catalog)
                <article class="catalog-card stack-sm">
                    <div class="catalog-card__media ratio-4x3">
                        @php $catalogCover = $catalog['cover'] ?? $placeholder('Catalog', 640, 480); @endphp
                        <img src="{{ $catalogCover }}" srcset="{{ $catalogCover }} 640w" sizes="(min-width: 62rem) 240px, 50vw" width="640" height="480" alt="{{ $catalog['title'] ?? 'Catalog' }}" loading="lazy">
                    </div>
                    <div class="stack-xs">
                        <h2>{{ $catalog['title'] ?? ($locale === 'en' ? 'Product lineup' : 'Ürün serisi') }}</h2>
                        @if(!empty($catalog['file']))
                            <a class="btn btn-outline" href="{{ $catalog['file'] }}" target="_blank" rel="noopener">{{ $locale === 'en' ? 'Open PDF' : 'PDF Aç' }}</a>
                        @else
                            <span class="btn btn-outline is-disabled">{{ $locale === 'en' ? 'Coming soon' : 'Çok yakında' }}</span>
                        @endif
                    </div>
                </article>
            @empty
                @for($i=0;$i<6;$i++)
                    <article class="catalog-card stack-sm placeholder">
                        <div class="catalog-card__media ratio-4x3 placeholder-block"></div>
                        <div class="stack-xs">
                            <h2>{{ $locale === 'en' ? 'Catalog title' : 'Katalog başlığı' }}</h2>
                            <span class="btn btn-outline is-disabled">{{ $locale === 'en' ? 'Coming soon' : 'Çok yakında' }}</span>
                        </div>
                    </article>
                @endfor
            @endforelse
        </div>
    </section>
@endsection
