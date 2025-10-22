@php($assetKey = 'products')
@extends('cms::site.layout')

@section('content')
    <section class="container py-5" data-analytics-section="products">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>{{ $locale === 'en' ? 'Products' : 'Ürünler' }}</h1>
        </div>
        <div class="row g-4">
            @foreach($products as $product)
                <div class="col-md-4">
                    <div class="card h-100">
                        @if(!empty($product['cover_image']))
                            <img src="{{ $product['cover_image'] }}" class="card-img-top" alt="{{ $product['name'] }}" loading="lazy" width="360" height="240">
                        @endif
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $product['name'] }}</h5>
                            <p class="card-text">{{ $product['short_desc'] }}</p>
                            <p class="mt-auto fw-semibold text-primary">{{ $locale === 'en' ? 'Contact us for price' : 'Fiyat için iletişime geçin' }}</p>
                            <a href="{{ $locale === 'en' ? url('/en/product/' . $product['slug']) : url('/urun/' . $product['slug']) }}" class="btn btn-outline-primary" data-analytics-click="product-detail">{{ $locale === 'en' ? 'View details' : 'Detay' }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection
