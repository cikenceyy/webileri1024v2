@php($assetKey = 'home')
@extends('cms::site.layout')

@section('critical-css')
    <style>
        .home-hero { display:flex; flex-direction:column; justify-content:center; min-height:60vh; background:#f5f7fb; padding:3rem 0; }
        .home-hero h1 { font-size:2.5rem; font-weight:700; }
        .home-hero p { max-width:40rem; }
    </style>
@endsection

@section('content')
    @php($blocks = $data['blocks'] ?? [])
    <section class="home-hero container" data-analytics-section="hero">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>{{ $blocks['hero']['title'] ?? '' }}</h1>
                <p class="lead">{{ $blocks['hero']['subtitle'] ?? '' }}</p>
                @if(!empty($blocks['hero']['cta_text']))
                    <a class="btn btn-primary" href="{{ $blocks['hero']['cta_link'] ?? '#' }}" data-analytics-click="hero-cta">{{ $blocks['hero']['cta_text'] }}</a>
                @endif
            </div>
            <div class="col-md-6 text-center">
                @if(!empty($blocks['hero']['image']))
                    <img src="{{ $blocks['hero']['image'] }}" alt="" class="img-fluid" loading="lazy" width="540" height="360">
                @endif
            </div>
        </div>
    </section>

    <section class="container my-5" data-analytics-section="usp">
        <div class="row g-4">
            @foreach($blocks['usp_grid'] ?? [] as $usp)
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        @if(!empty($usp['icon']))
                            <img src="{{ $usp['icon'] }}" alt="" class="mb-3" loading="lazy" width="64" height="64">
                        @endif
                        <h5>{{ $usp['title'] ?? '' }}</h5>
                        <p class="text-muted">{{ $usp['description'] ?? '' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="container my-5" data-analytics-section="featured-products">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 mb-0">{{ $locale === 'en' ? 'Featured Products' : 'Öne Çıkan Ürünler' }}</h2>
            <a href="{{ $locale === 'en' ? url('/en/products') : url('/urunler') }}" class="btn btn-link">{{ $locale === 'en' ? 'See all' : 'Tümünü Gör' }}</a>
        </div>
        <div class="row g-4">
            @foreach($featuredProducts as $product)
                <div class="col-md-4">
                    <div class="card h-100">
                        @if(!empty($product['cover_image']))
                            <img src="{{ $product['cover_image'] }}" class="card-img-top" alt="{{ $product['name'] }}" loading="lazy" width="360" height="240">
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $product['name'] }}</h5>
                            <p class="card-text">{{ $product['short_desc'] }}</p>
                            <a href="{{ $locale === 'en' ? url('/en/product/' . $product['slug']) : url('/urun/' . $product['slug']) }}" class="btn btn-outline-primary" data-analytics-click="mini-product">{{ $locale === 'en' ? 'View details' : 'Detay' }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="container my-5" data-analytics-section="catalogs">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 mb-0">{{ $locale === 'en' ? 'Catalogs' : 'Kataloglar' }}</h2>
            <a href="{{ $locale === 'en' ? url('/en/catalogs') : url('/kataloglar') }}" class="btn btn-link">{{ $locale === 'en' ? 'See all' : 'Tümünü Gör' }}</a>
        </div>
        <div class="row g-4">
            @foreach($catalogs as $catalog)
                <div class="col-md-4">
                    <div class="card h-100">
                        @if(!empty($catalog['cover']))
                            <img src="{{ $catalog['cover'] }}" class="card-img-top" alt="{{ $catalog['title'] ?? '' }}" loading="lazy" width="360" height="240">
                        @endif
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $catalog['title'] ?? '' }}</h5>
                            <a href="{{ $catalog['file'] ?? '#' }}" target="_blank" rel="noopener" class="btn btn-outline-primary mt-auto" data-analytics-click="catalog-download">{{ $locale === 'en' ? 'Open PDF' : 'PDF Aç' }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="py-5 bg-light" data-analytics-section="cta">
        <div class="container text-center">
            <h2>{{ $blocks['cta_band']['title'] ?? '' }}</h2>
            @if(!empty($blocks['cta_band']['cta_text']))
                <a class="btn btn-primary mt-3" href="{{ $blocks['cta_band']['cta_link'] ?? '#' }}" data-analytics-click="cta-band">{{ $blocks['cta_band']['cta_text'] }}</a>
            @endif
        </div>
    </section>
@endsection
