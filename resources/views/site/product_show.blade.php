@extends('site.layout', ['pageId' => 'product-show', 'locale' => $locale ?? app()->getLocale()])

@push('site-styles')
    @vite('resources/scss/site/product-show.scss')
@endpush

@push('site-scripts')
    @vite('resources/js/site/product-show.js')
@endpush

@section('content')
    @php
        $placeholder = fn ($label, $width, $height) => 'data:image/svg+xml;utf8,' . rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$width} {$height}'><rect width='100%' height='100%' fill='%23e4e7ec'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='Inter, sans-serif' font-size='24' fill='%23667085'>{$label}</text></svg>");
    @endphp

    <section class="pattern-product-hero" data-module="reveal">
        <div class="product-hero grid">
            <div class="stack-lg">
                <a class="back-link" href="{{ $locale === 'en' ? route('cms.en.products') : route('cms.products') }}">&larr; {{ $locale === 'en' ? 'Back to products' : 'Ürünlere dön' }}</a>
                <h1>{{ $product['name'] }}</h1>
                <p class="lead">{{ $product['short_desc'] ?? ($locale === 'en' ? 'Request full specifications from our team.' : 'Detaylı teknik bilgileri ekibimizden isteyin.') }}</p>
                <ul class="product-meta stack-xs">
                    <li><strong>SKU:</strong> {{ $product['sku'] ?? '—' }}</li>
                    <li>{{ $locale === 'en' ? 'Price available on request' : 'Fiyat bilgi için iletişime geçin' }}</li>
                </ul>
                <a class="btn btn-primary" href="{{ $locale === 'en' ? route('cms.en.contact') : route('cms.contact') }}">{{ $locale === 'en' ? 'Contact for pricing' : 'Fiyat için iletişime geçin' }}</a>
            </div>
            <div class="product-gallery" data-module="light-gallery">
                <div class="gallery-main ratio-4x3">
                    @php $cover = $product['cover_image'] ?? $placeholder('Product', 960, 720); @endphp
                    <img src="{{ $cover }}" srcset="{{ $cover }} 960w, {{ $cover }} 640w" sizes="(min-width: 62rem) 560px, 100vw" width="960" height="720" alt="{{ $product['name'] }}" loading="eager">
                </div>
                @if(!empty($product['gallery']))
                    <div class="gallery-thumbs cluster" role="list">
                        @foreach($product['gallery'] as $media)
                            @php $thumb = $media ?: $placeholder('Media', 120, 90); @endphp
                            <button type="button" class="thumb" data-gallery-src="{{ $thumb }}">
                                <img src="{{ $thumb }}" width="120" height="90" alt="{{ $product['name'] }} thumbnail" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
