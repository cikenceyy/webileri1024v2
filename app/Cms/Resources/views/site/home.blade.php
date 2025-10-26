@extends('cms::site.layout', ['pageId' => 'home', 'locale' => $locale ?? app()->getLocale()])

@push('critical-css')
    <style>
        .home-hero{display:grid;gap:clamp(var(--space-24),5vw,var(--space-40));align-items:center}
        @media (min-width:48rem){.home-hero{grid-template-columns:repeat(2,minmax(0,1fr));}}
        .home-hero__media{position:relative;border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm)}
        .home-hero__media img{width:100%;height:100%;object-fit:cover;display:block}
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
        $industryItems = data_get($data, 'blocks.industries', []);
        $processSteps = data_get($data, 'blocks.process_steps', []);
        $statsItems = data_get($data, 'blocks.stats_band', []);
        $partners = data_get($data, 'blocks.partners', []);
        $ctaBand = data_get($data, 'blocks.cta_band', []);
        $placeholder = fn ($label, $width, $height) => 'data:image/svg+xml;utf8,' . rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$width} {$height}'><rect width='100%' height='100%' fill='%23e4e7ec'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='Inter, sans-serif' font-size='24' fill='%23667085'>{$label}</text></svg>");
        $heroImage = $hero['image'] ?? $placeholder('Hero', 1280, 720);
        $uspFallbacks = \Illuminate\Support\Facades\Lang::get('cms::site.home.usp.defaults', [], $pageLocale);
        $industryFallbacks = \Illuminate\Support\Facades\Lang::get('cms::site.home.industries.defaults', [], $pageLocale);
        $processFallbacks = \Illuminate\Support\Facades\Lang::get('cms::site.home.process.defaults', [], $pageLocale);
        $statsFallbacks = \Illuminate\Support\Facades\Lang::get('cms::site.home.stats.defaults', [], $pageLocale);
        $productFallbacks = \Illuminate\Support\Facades\Lang::get('cms::site.home.products.placeholders', [], $pageLocale);
        $catalogFallbacks = \Illuminate\Support\Facades\Lang::get('cms::site.home.catalogs.placeholders', [], $pageLocale);
    @endphp

    <section class="section section--hero" data-module="reveal">
        <div class="container container--wide">
            <div class="pattern-hero home-hero">
                <div class="stack-lg">
                    <p class="eyebrow">{{ __('cms::site.home.eyebrow') }}</p>
                    <h1 class="display">{{ $hero['title'] ?? __('cms::site.home.hero.title') }}</h1>
                    <p class="lead">{{ $hero['subtitle'] ?? __('cms::site.home.hero.subtitle') }}</p>
                    <div class="cluster">
                        <a class="btn btn-primary" data-module="beacon" data-beacon-event="home-cta" data-beacon-payload="hero"
                           href="{{ !empty($hero['cta_link']) && !empty($hero['cta_text']) ? $hero['cta_link'] : ($pageLocale === 'en' ? route('cms.en.contact') : route('cms.contact')) }}">
                            {{ $hero['cta_text'] ?? __('cms::site.home.hero.cta_default') }}
                        </a>
                    </div>
                </div>
                <div class="home-hero__media" data-module="lazy-media">
                    <img src="{{ $heroImage }}" srcset="{{ $heroImage }} 1280w, {{ $heroImage }} 640w"
                         sizes="(min-width: 62rem) 50vw, 100vw" width="1280" height="720"
                         alt="{{ $hero['title'] ?? 'Automation hero image' }}" loading="eager">
                </div>
            </div>
        </div>
    </section>

    <section class="section" data-module="reveal">
        <div class="container">
            <div class="pattern-usp">
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
            </div>
        </div>
    </section>

    <section class="section" data-module="reveal">
        <div class="container">
            <div class="pattern-industries">
                <div class="section-head stack-sm">
                    <h2>{{ __('cms::site.home.industries.heading') }}</h2>
                    <p>{{ __('cms::site.home.industries.subheading') }}</p>
                </div>
                <div class="industry-grid grid-auto">
                    @forelse($industryItems as $item)
                        <article class="industry-card stack-sm">
                            @if(!empty($item['icon']))
                                <img src="{{ $item['icon'] }}" width="56" height="56" alt="" loading="lazy">
                            @endif
                            <h3>{{ $item['title'] ?? __('cms::site.home.industries.default_title') }}</h3>
                            <p>{{ $item['description'] ?? __('cms::site.home.industries.default_description') }}</p>
                        </article>
                    @empty
                        @foreach((array) $industryFallbacks as $fallback)
                            <article class="industry-card stack-sm" data-skeleton>
                                <div class="placeholder-icon" aria-hidden="true"></div>
                                <h3>{{ $fallback['title'] ?? __('cms::site.home.industries.default_title') }}</h3>
                                <p>{{ $fallback['description'] ?? __('cms::site.home.industries.default_description') }}</p>
                            </article>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="section" data-module="reveal">
        <div class="container">
            <div class="pattern-process">
                <div class="section-head stack-sm">
                    <h2>{{ __('cms::site.home.process.heading') }}</h2>
                    <p>{{ __('cms::site.home.process.subheading') }}</p>
                </div>
                <ol class="process-steps">
                    @forelse($processSteps as $step)
                        <li class="process-step stack-sm">
                            <div class="process-step__index">{{ $step['step_no'] ?? __('cms::site.home.process.step_prefix') }}</div>
                            <div class="process-step__body stack-xs">
                                <h3>{{ $step['title'] ?? __('cms::site.home.process.default_title') }}</h3>
                                <p>{{ $step['description'] ?? __('cms::site.home.process.default_description') }}</p>
                                @if(!empty($step['image']))
                                    <div class="process-step__media ratio-4x3" data-module="lazy-media">
                                        <img src="{{ $step['image'] }}" width="960" height="720" alt="{{ $step['title'] ?? 'Process step' }}" loading="lazy">
                                    </div>
                                @endif
                            </div>
                        </li>
                    @empty
                        @foreach((array) $processFallbacks as $fallback)
                            <li class="process-step stack-sm" data-skeleton>
                                <div class="process-step__index">{{ $fallback['step'] ?? __('cms::site.home.process.step_prefix') }}</div>
                                <div class="process-step__body stack-xs">
                                    <h3>{{ $fallback['title'] ?? __('cms::site.home.process.default_title') }}</h3>
                                    <p>{{ $fallback['description'] ?? __('cms::site.home.process.default_description') }}</p>
                                </div>
                            </li>
                        @endforeach
                    @endforelse
                </ol>
            </div>
        </div>
    </section>

    <section class="section section--subtle" data-module="reveal">
        <div class="container">
            <div class="pattern-stats">
                <div class="section-head stack-sm">
                    <h2>{{ __('cms::site.home.stats.heading') }}</h2>
                    <p>{{ __('cms::site.home.stats.subheading') }}</p>
                </div>
                <div class="stats-grid">
                    @forelse($statsItems as $item)
                        <div class="stat-card stack-xs">
                            <span class="stat-card__value">{{ $item['value'] ?? __('cms::site.home.stats.default_value') }}</span>
                            <span class="stat-card__label">{{ $item['label'] ?? __('cms::site.home.stats.default_label') }}</span>
                        </div>
                    @empty
                        @foreach((array) $statsFallbacks as $fallback)
                            <div class="stat-card stack-xs" data-skeleton>
                                <span class="stat-card__value">{{ $fallback['value'] ?? __('cms::site.home.stats.default_value') }}</span>
                                <span class="stat-card__label">{{ $fallback['label'] ?? __('cms::site.home.stats.default_label') }}</span>
                            </div>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="section" data-module="reveal skeletons">
        <div class="container">
            <div class="pattern-product-grid">
                <div class="section-head stack-sm">
                    <h2>{{ __('cms::site.home.products.heading') }}</h2>
                    <p>{{ __('cms::site.home.products.subheading') }}</p>
                </div>
                <div class="product-grid grid-auto">
                    @forelse($featuredProducts as $product)
                        <article class="product-card stack-sm">
                            <div class="product-card__media ratio-4x3">
                                @php $productCover = $product['cover_image'] ?? $placeholder('Product', 640, 480); @endphp
                                <img src="{{ $productCover }}" srcset="{{ $productCover }} 640w" sizes="(min-width: 62rem) 240px, 50vw"
                                     width="640" height="480" alt="{{ $product['name'] ?? 'Product' }}" loading="lazy">
                            </div>
                            <div class="stack-xs">
                                <h3>{{ $product['name'] ?? __('cms::site.home.products.placeholder_title') }}</h3>
                                <p>{{ $product['short_desc'] ?? __('cms::site.home.products.placeholder_description') }}</p>
                                @php
                                    $productUrl = !empty($product['slug'])
                                        ? ($pageLocale === 'en' ? route('cms.en.product.show', $product['slug']) : route('cms.product.show', $product['slug']))
                                        : ($pageLocale === 'en' ? route('cms.en.products') : route('cms.products'));
                                @endphp
                                <a class="btn btn-outline" data-module="beacon" data-beacon-event="mini-product-view"
                                   data-beacon-payload="{{ $product['slug'] ?? 'product' }}" href="{{ $productUrl }}">
                                    {{ __('cms::site.home.products.cta') }}
                                </a>
                            </div>
                        </article>
                    @empty
                        @foreach((array) $productFallbacks as $fallback)
                            <article class="product-card stack-sm placeholder" data-skeleton>
                                <div class="product-card__media ratio-4x3 placeholder-block"></div>
                                <div class="stack-xs">
                                    <h3>{{ $fallback['title'] ?? __('cms::site.home.products.placeholder_title') }}</h3>
                                    <p>{{ $fallback['description'] ?? __('cms::site.home.products.placeholder_description') }}</p>
                                    <span class="btn btn-outline is-disabled">{{ __('cms::site.home.products.placeholder_cta') }}</span>
                                </div>
                            </article>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="section" data-module="reveal skeletons">
        <div class="container">
            <div class="pattern-catalog-grid">
                <div class="section-head stack-sm">
                    <h2>{{ __('cms::site.home.catalogs.heading') }}</h2>
                    <p>{{ __('cms::site.home.catalogs.subheading') }}</p>
                </div>
                <div class="catalog-grid grid-auto">
                    @forelse($catalogs as $catalog)
                        <article class="catalog-card stack-sm">
                            <div class="catalog-card__media ratio-4x3">
                                @php $catalogCover = $catalog['cover'] ?? $placeholder('Catalog', 640, 480); @endphp
                                <img src="{{ $catalogCover }}" srcset="{{ $catalogCover }} 640w" sizes="(min-width: 62rem) 240px, 50vw"
                                     width="640" height="480" alt="{{ $catalog['title'] ?? 'Catalog' }}" loading="lazy">
                            </div>
                            <div class="stack-xs">
                                <h3>{{ $catalog['title'] ?? __('cms::site.home.catalogs.placeholder_title') }}</h3>
                                <a class="btn btn-outline" data-module="beacon" data-beacon-event="catalog-open" data-beacon-payload="home"
                                   href="{{ $catalog['file'] ?? '#' }}" target="_blank" rel="noopener">
                                    {{ __('cms::site.home.catalogs.cta') }}
                                </a>
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
            </div>
        </div>
    </section>

    <section class="section" data-module="reveal">
        <div class="container">
            <div class="pattern-partners">
                <div class="section-head stack-sm">
                    <h2>{{ __('cms::site.home.partners.heading') }}</h2>
                    <p>{{ __('cms::site.home.partners.subheading') }}</p>
                </div>
                <div class="partner-rail">
                    @forelse($partners as $partner)
                        <a class="partner-logo"
                           @if(!empty($partner['link'])) href="{{ $partner['link'] }}" target="_blank" rel="noopener"
                           @else href="javascript:void(0)" aria-disabled="true" @endif>
                            @php $logo = $partner['logo'] ?? null; @endphp
                            @if($logo)
                                <img src="{{ $logo }}" width="160" height="80" alt="{{ $partner['name'] ?? __('cms::site.home.partners.placeholder') }}" loading="lazy">
                            @else
                                <span>{{ $partner['name'] ?? __('cms::site.home.partners.placeholder') }}</span>
                            @endif
                        </a>
                    @empty
                        <span class="partner-logo placeholder" data-skeleton>{{ __('cms::site.home.partners.placeholder') }}</span>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="section" data-module="reveal">
        <div class="container container--wide">
            <div class="pattern-feature">
                <div class="feature-band stack-sm">
                    <h2>{{ $ctaBand['title'] ?? __('cms::site.home.feature.title') }}</h2>
                    <p>{{ __('cms::site.home.feature.subtitle') }}</p>
                    <a class="btn btn-primary" data-module="beacon" data-beacon-event="home-cta"
                       data-beacon-payload="cta-band"
                       href="{{ $ctaBand['cta_link'] ?? ($pageLocale === 'en' ? route('cms.en.contact') : route('cms.contact')) }}">
                        {{ $ctaBand['cta_text'] ?? __('cms::site.home.feature.cta') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
