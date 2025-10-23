@extends('cms::site.layout', ['pageId' => 'home', 'locale' => $locale ?? app()->getLocale()])

@push('critical-css')
    <style>
        .home-hero{display:grid;gap:var(--space-24);align-items:center;padding-block:clamp(4rem,6vw,6rem)}
        @media (min-width: 48rem){.home-hero{grid-template-columns:repeat(2,minmax(0,1fr));}}
        .home-hero__media{position:relative;border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);}
        .home-hero__media img{width:100%;height:100%;object-fit:cover;display:block;}
    </style>
@endpush

@push('site-styles')
    @vite('app/Cms/Resources/assets/scss/site/home.scss')
@endpush

@push('site-scripts')
    @vite('app/Cms/Resources/assets/js/site/home.js')
@endpush

@section('content')
    @php
        $pageLocale = $locale ?? app()->getLocale();
        $hero = data_get($data, 'blocks.hero', []);
        $uspItems = data_get($data, 'blocks.usp_grid', []);
        $ctaBand = data_get($data, 'blocks.cta_band', []);
        $placeholder = fn ($label, $width, $height) => 'data:image/svg+xml;utf8,' . rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$width} {$height}'><rect width='100%' height='100%' fill='%23e4e7ec'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='Inter, sans-serif' font-size='24' fill='%23667085'>{$label}</text></svg>");
        $heroImage = $hero['image'] ?? $placeholder('Hero', 1280, 720);
        $uspFallbacks = \Illuminate\Support\Facades\Lang::get('cms::site.home.usp.defaults', [], $pageLocale);
        $productFallbacks = \Illuminate\Support\Facades\Lang::get('cms::site.home.products.placeholders', [], $pageLocale);
        $catalogFallbacks = \Illuminate\Support\Facades\Lang::get('cms::site.home.catalogs.placeholders', [], $pageLocale);
    @endphp
    <section class="pattern-hero home-hero" data-module="reveal">
        <div class="stack-lg">
            <p class="eyebrow">{{ __('cms::site.home.eyebrow') }}</p>
            <h1 class="display">{{ $hero['title'] ?? __('cms::site.home.hero.title') }}</h1>
            <p class="lead">{{ $hero['subtitle'] ?? __('cms::site.home.hero.subtitle') }}</p>
            @if(!empty($hero['cta_link']) && !empty($hero['cta_text']))
                <div class="cluster">
                    <a class="btn btn-primary" data-module="beacon" data-beacon-event="home.hero.cta" href="{{ $hero['cta_link'] }}">{{ $hero['cta_text'] }}</a>
                </div>
            @else
                <div class="cluster">
                    <a class="btn btn-primary" data-module="beacon" data-beacon-event="home.hero.cta" href="{{ $pageLocale === 'en' ? route('cms.en.contact') : route('cms.contact') }}">{{ __('cms::site.home.hero.cta_default') }}</a>
                </div>
            @endif
        </div>
        <div class="home-hero__media" data-module="lazy-media">
            <img src="{{ $heroImage }}" srcset="{{ $heroImage }} 1280w, {{ $heroImage }} 640w" sizes="(min-width: 62rem) 50vw, 100vw" width="1280" height="720" alt="{{ $hero['title'] ?? 'Automation hero image' }}" loading="eager">
        </div>
    </section>

    <section class="pattern-usp" data-module="reveal">
        <div class="section-head stack-sm">
            <h2>{{ __('cms::site.home.usp.heading') }}</h2>
            <p>{{ __('cms::site.home.usp.subheading') }}</p>
        </div>
        <div class="usp-grid grid-auto">
            @forelse($uspItems as $item)
                <article class="usp-card stack-sm">
                    @if(!empty($item['icon']))
                        <img class="usp-card__icon" src="{{ $item['icon'] }}" width="48" height="48" alt="" loading="lazy">
                    @endif
                    <h3>{{ $item['title'] ?? __('cms::site.home.usp.default_title') }}</h3>
                    <p>{{ $item['description'] ?? __('cms::site.home.usp.default_description') }}</p>
                </article>
            @empty
                @foreach((array) $uspFallbacks as $fallback)
                    <article class="usp-card stack-sm" data-skeleton>
                        <div class="usp-card__icon placeholder-icon" aria-hidden="true"></div>
                        <h3>{{ $fallback['title'] ?? __('cms::site.home.usp.default_title') }}</h3>
                        <p>{{ $fallback['description'] ?? __('cms::site.home.usp.default_description') }}</p>
                    </article>
                @endforeach
            @endforelse
        </div>
    </section>

    <section class="pattern-product-grid" data-module="reveal skeletons">
        <div class="section-head stack-sm">
            <h2>{{ __('cms::site.home.products.heading') }}</h2>
            <p>{{ __('cms::site.home.products.subheading') }}</p>
        </div>
        <div class="product-grid grid-auto">
            @forelse($featuredProducts as $product)
                <article class="product-card stack-sm">
                    <div class="product-card__media ratio-3x2">
                        @php $productCover = $product['cover_image'] ?? $placeholder('Product', 640, 480); @endphp
                        <img src="{{ $productCover }}" srcset="{{ $productCover }} 640w" sizes="(min-width: 62rem) 240px, 50vw" width="640" height="480" alt="{{ $product['name'] ?? 'Product' }}" loading="lazy">
                    </div>
                    <div class="stack-xs">
                        <h3>{{ $product['name'] ?? __('cms::site.home.products.placeholder_title') }}</h3>
                        <p>{{ $product['short_desc'] ?? __('cms::site.home.products.placeholder_description') }}</p>
                        @php
                            $productUrl = !empty($product['slug'])
                                ? ($pageLocale === 'en' ? route('cms.en.product.show', $product['slug']) : route('cms.product.show', $product['slug']))
                                : ($pageLocale === 'en' ? route('cms.en.products') : route('cms.products'));
                        @endphp
                        <a class="btn btn-outline" href="{{ $productUrl }}">{{ __('cms::site.home.products.cta') }}</a>
                    </div>
                </article>
            @empty
                @foreach((array) $productFallbacks as $fallback)
                    <article class="product-card stack-sm placeholder" data-skeleton>
                        <div class="product-card__media ratio-3x2 placeholder-block"></div>
                        <div class="stack-xs">
                            <h3>{{ $fallback['title'] ?? __('cms::site.home.products.placeholder_title') }}</h3>
                            <p>{{ $fallback['description'] ?? __('cms::site.home.products.placeholder_description') }}</p>
                            <span class="btn btn-outline is-disabled">{{ __('cms::site.home.products.placeholder_cta') }}</span>
                        </div>
                    </article>
                @endforeach
            @endforelse
        </div>
    </section>

    <section class="pattern-catalog-grid" data-module="reveal skeletons">
        <div class="section-head stack-sm">
            <h2>{{ __('cms::site.home.catalogs.heading') }}</h2>
            <p>{{ __('cms::site.home.catalogs.subheading') }}</p>
        </div>
        <div class="catalog-grid grid-auto">
            @forelse($catalogs as $catalog)
                <article class="catalog-card stack-sm">
                    <div class="catalog-card__media ratio-4x3">
                        @php $catalogCover = $catalog['cover'] ?? $placeholder('Catalog', 640, 480); @endphp
                        <img src="{{ $catalogCover }}" srcset="{{ $catalogCover }} 640w" sizes="(min-width: 62rem) 240px, 50vw" width="640" height="480" alt="{{ $catalog['title'] ?? 'Catalog' }}" loading="lazy">
                    </div>
                    <div class="stack-xs">
                        <h3>{{ $catalog['title'] ?? __('cms::site.home.catalogs.placeholder_title') }}</h3>
                        <a class="btn btn-outline" data-module="beacon" data-beacon-event="home.catalog.open" href="{{ $catalog['file'] ?? '#' }}" target="_blank" rel="noopener">{{ __('cms::site.home.catalogs.cta') }}</a>
                    </div>
                </article>
            @empty
                @foreach((array) $catalogFallbacks as $fallback)
                    <article class="catalog-card stack-sm placeholder" data-skeleton>
                        <div class="catalog-card__media ratio-4x3 placeholder-block"></div>
                        <div class="stack-xs">
                            <h3>{{ $fallback['title'] ?? __('cms::site.home.catalogs.placeholder_title') }}</h3>
                            <span class="btn btn-outline is-disabled">{{ __('cms::site.home.catalogs.placeholder_cta') }}</span>
                        </div>
                    </article>
                @endforeach
            @endforelse
        </div>
    </section>

    <section class="pattern-feature" data-module="reveal">
        <div class="feature-band stack-sm">
            <h2>{{ $ctaBand['title'] ?? __('cms::site.home.feature.title') }}</h2>
            <p>{{ __('cms::site.home.feature.subtitle') }}</p>
            <a class="btn btn-primary" data-module="beacon" data-beacon-event="home.feature.cta" href="{{ $ctaBand['cta_link'] ?? ($pageLocale === 'en' ? route('cms.en.contact') : route('cms.contact')) }}">{{ $ctaBand['cta_text'] ?? __('cms::site.home.feature.cta') }}</a>
        </div>
    </section>
@endsection
