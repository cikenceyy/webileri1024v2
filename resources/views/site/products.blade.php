@extends('site.layout', ['pageId' => 'products', 'locale' => $locale ?? app()->getLocale()])

@push('site-styles')
    @vite('resources/scss/site/products.scss')
@endpush

@push('site-scripts')
    @vite('resources/js/site/products.js')
@endpush

@section('content')
    @php
        $placeholder = fn ($label, $width, $height) => 'data:image/svg+xml;utf8,' . rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$width} {$height}'><rect width='100%' height='100%' fill='%23e4e7ec'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='Inter, sans-serif' font-size='24' fill='%23667085'>{$label}</text></svg>");
    @endphp

    @php $hero = data_get($data, 'blocks.hero', []); @endphp
    <section class="pattern-hero products-hero" data-module="reveal">
        <div class="stack-lg">
            <h1>{{ $hero['title'] ?? ($locale === 'en' ? 'Product portfolio' : 'Ürün portföyü') }}</h1>
            <p class="lead">{{ $hero['subtitle'] ?? ($locale === 'en' ? 'Browse modular equipment ready for immediate integration.' : 'Entegrasyona hazır modüler ekipmanları keşfedin.') }}</p>
        </div>
    </section>

    <section class="pattern-product-grid" data-module="reveal">
        <div class="product-grid grid-auto">
            @forelse($products as $product)
                <article class="product-card stack-sm">
                    <div class="product-card__media ratio-3x2">
                        @php $productCover = $product['cover_image'] ?? $placeholder('Product', 640, 480); @endphp
                        <img src="{{ $productCover }}" srcset="{{ $productCover }} 640w" sizes="(min-width: 62rem) 240px, 50vw" width="640" height="480" alt="{{ $product['name'] }}" loading="lazy">
                    </div>
                    <div class="stack-xs">
                        <h2>{{ $product['name'] }}</h2>
                        <p>{{ $product['short_desc'] ?? ($locale === 'en' ? 'Detailed specs available on request.' : 'Detaylı özellikler talep üzerine sunulur.') }}</p>
                        @php
                            $productUrl = !empty($product['slug'])
                                ? ($locale === 'en' ? route('cms.en.product.show', $product['slug']) : route('cms.product.show', $product['slug']))
                                : ($locale === 'en' ? route('cms.en.products') : route('cms.products'));
                        @endphp
                        <a class="btn btn-outline" href="{{ $productUrl }}">{{ $locale === 'en' ? 'View details' : 'Detayları gör' }}</a>
                    </div>
                </article>
            @empty
                @for($i=0;$i<6;$i++)
                    <article class="product-card stack-sm placeholder">
                        <div class="product-card__media ratio-3x2 placeholder-block"></div>
                        <div class="stack-xs">
                            <h2>{{ $locale === 'en' ? 'Product name' : 'Ürün adı' }}</h2>
                            <p>{{ $locale === 'en' ? 'Description will appear soon.' : 'Açıklama yakında görünecek.' }}</p>
                            <span class="btn btn-outline is-disabled">{{ $locale === 'en' ? 'Coming soon' : 'Çok yakında' }}</span>
                        </div>
                    </article>
                @endfor
            @endforelse
        </div>
    </section>
@endsection
