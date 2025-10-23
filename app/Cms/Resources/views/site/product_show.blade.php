@php($assetKey = 'product-show')
@extends('cms::site.layout')

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product['name'] ?? '',
            'description' => $product['short_desc'] ?? '',
            'sku' => $product['sku'] ?? '',
            'image' => array_filter(array_merge(
                !empty($product['cover_image']) ? [$product['cover_image']] : [],
                $product['gallery'] ?? []
            )),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
@endpush

@section('content')
    <section class="container py-5" data-analytics-section="product-detail">
        <div class="row g-5">
            <div class="col-md-6">
                @if(!empty($product['cover_image']))
                    <img src="{{ $product['cover_image'] }}" class="img-fluid mb-3" alt="{{ $product['name'] }}" loading="lazy" width="540" height="360">
                @endif
                <div class="row g-3">
                    @foreach(($product['gallery'] ?? []) as $image)
                        <div class="col-6">
                            <img src="{{ $image }}" class="img-fluid rounded" alt="{{ $product['name'] }}" loading="lazy" width="260" height="180">
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-6">
                <h1>{{ $product['name'] }}</h1>
                <p class="text-muted">SKU: {{ $product['sku'] }}</p>
                <p class="lead">{{ $product['short_desc'] }}</p>
                <div class="alert alert-info">{{ $locale === 'en' ? 'Contact us for price information.' : 'Fiyat için iletişime geçin.' }}</div>
                <a href="{{ $locale === 'en' ? url('/en/contact') : url('/iletisim') }}" class="btn btn-primary" data-analytics-click="product-contact">{{ $locale === 'en' ? 'Contact us' : 'İletişime geçin' }}</a>
            </div>
        </div>
    </section>
@endsection
