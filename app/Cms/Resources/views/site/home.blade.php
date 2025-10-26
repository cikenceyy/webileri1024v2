@extends('cms::site.layout', ['pageId' => 'home', 'locale' => $locale ?? app()->getLocale()])

@push('critical-css')
    <style>
        .p-hero{display:grid;gap:clamp(var(--space-24),5vw,var(--space-40));align-items:center}
        @media (min-width:48rem){.p-hero{grid-template-columns:repeat(2,minmax(0,1fr));}}
        .p-hero__media{position:relative;border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm)}
        .p-hero__media img{width:100%;height:100%;object-fit:cover;display:block}
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
        $featuredProducts = $featuredProducts ?? [];
        $featuredCatalogs = $catalogs ?? ($featuredCatalogs ?? []);
    @endphp

    <div class="home-page">
        <section class="p-section p-section--hero" data-module="reveal">
            <div class="u-container u-container--wide u-stack-32">
                <div class="p-hero">
                    <div class="u-stack-16">
                        <p class="p-eyebrow">{{ __('cms::site.home.eyebrow') }}</p>
                        <h1 class="p-display">{{ $hero['title'] ?? __('cms::site.home.hero.title') }}</h1>
                        <p class="p-lead">{{ $hero['subtitle'] ?? __('cms::site.home.hero.subtitle') }}</p>
                        <div class="u-cluster">
                            <a class="c-button c-button--primary" data-module="beacon" data-beacon-event="home-cta" data-beacon-payload="hero"
                               href="{{ !empty($hero['cta_link']) && !empty($hero['cta_text']) ? $hero['cta_link'] : ($pageLocale === 'en' ? route('cms.en.contact') : route('cms.contact')) }}">
                                {{ $hero['cta_text'] ?? __('cms::site.home.hero.cta_default') }}
                            </a>
                        </div>
                    </div>
                    <div class="p-hero__media" data-module="lazy-media">
                        <img src="{{ $heroImage }}" srcset="{{ $heroImage }} 1280w, {{ $heroImage }} 640w"
                             sizes="(min-width: 62rem) 50vw, 100vw" width="1280" height="720"
                             alt="{{ $hero['title'] ?? 'Automation hero image' }}" loading="eager">
                    </div>
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-stack-32">
                <div class="p-section__head u-stack-12">
                    <h2>{{ __('cms::site.home.usp.heading') }}</h2>
                    <p>{{ __('cms::site.home.usp.subheading') }}</p>
                </div>
                <div class="p-usp__grid">
                    @forelse($uspItems as $item)
                        <article class="c-card c-card--usp u-stack-12">
                            @if(!empty($item['icon']))
                                <img class="c-card__icon" src="{{ $item['icon'] }}" width="48" height="48" alt="" loading="lazy">
                            @endif
                            <h3>{{ $item['title'] ?? __('cms::site.home.usp.default_title') }}</h3>
                            <p class="u-text-secondary">{{ $item['description'] ?? __('cms::site.home.usp.default_description') }}</p>
                        </article>
                    @empty
                        @foreach((array) $uspFallbacks as $fallback)
                            <article class="c-card c-card--usp u-stack-12" data-skeleton>
                                <div class="c-card__icon" aria-hidden="true"></div>
                                <h3>{{ $fallback['title'] ?? __('cms::site.home.usp.default_title') }}</h3>
                                <p class="u-text-secondary">{{ $fallback['description'] ?? __('cms::site.home.usp.default_description') }}</p>
                            </article>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-stack-32">
                <div class="p-section__head u-stack-12">
                    <h2>{{ __('cms::site.home.industries.heading') }}</h2>
                    <p>{{ __('cms::site.home.industries.subheading') }}</p>
                </div>
                <div class="p-industries__grid">
                    @forelse($industryItems as $item)
                        <article class="c-card c-card--industry u-stack-12">
                            @if(!empty($item['icon']))
                                <img class="c-card__icon" src="{{ $item['icon'] }}" width="56" height="56" alt="" loading="lazy">
                            @endif
                            <h3>{{ $item['title'] ?? __('cms::site.home.industries.default_title') }}</h3>
                            <p class="u-text-secondary">{{ $item['description'] ?? __('cms::site.home.industries.default_description') }}</p>
                        </article>
                    @empty
                        @foreach((array) $industryFallbacks as $fallback)
                            <article class="c-card c-card--industry u-stack-12" data-skeleton>
                                <div class="c-card__icon" aria-hidden="true"></div>
                                <h3>{{ $fallback['title'] ?? __('cms::site.home.industries.default_title') }}</h3>
                                <p class="u-text-secondary">{{ $fallback['description'] ?? __('cms::site.home.industries.default_description') }}</p>
                            </article>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-stack-32">
                <div class="p-section__head u-stack-12">
                    <h2>{{ __('cms::site.home.process.heading') }}</h2>
                    <p>{{ __('cms::site.home.process.subheading') }}</p>
                </div>
                <div class="p-process">
                    @forelse($processSteps as $step)
                        <div class="p-process__item u-stack-12">
                            <span class="p-process__index">{{ $step['step_no'] ?? __('cms::site.home.process.step_prefix') }}</span>
                            <div class="u-stack-12">
                                <h3>{{ $step['title'] ?? __('cms::site.home.process.default_title') }}</h3>
                                <p class="u-text-secondary">{{ $step['description'] ?? __('cms::site.home.process.default_description') }}</p>
                                @if(!empty($step['image']))
                                    <div class="p-process__media u-ratio-4x3" data-module="lazy-media">
                                        <img src="{{ $step['image'] }}" width="960" height="720" alt="{{ $step['title'] ?? 'Process step' }}" loading="lazy">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        @foreach((array) $processFallbacks as $fallback)
                            <div class="p-process__item u-stack-12" data-skeleton>
                                <span class="p-process__index">{{ $fallback['step'] ?? __('cms::site.home.process.step_prefix') }}</span>
                                <div class="u-stack-12">
                                    <h3>{{ $fallback['title'] ?? __('cms::site.home.process.default_title') }}</h3>
                                    <p class="u-text-secondary">{{ $fallback['description'] ?? __('cms::site.home.process.default_description') }}</p>
                                </div>
                            </div>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </section>

        <section class="p-section p-section--muted" data-module="reveal">
            <div class="u-container u-stack-32">
                <div class="p-section__head u-stack-12">
                    <h2>{{ __('cms::site.home.stats.heading') }}</h2>
                    <p>{{ __('cms::site.home.stats.subheading') }}</p>
                </div>
                <div class="home-page p-stats">
                    @forelse($statsItems as $item)
                        <div class="p-stats__card u-stack-8">
                            <span class="p-stats__value">{{ $item['value'] ?? __('cms::site.home.stats.default_value') }}</span>
                            <span>{{ $item['label'] ?? __('cms::site.home.stats.default_label') }}</span>
                        </div>
                    @empty
                        @foreach((array) $statsFallbacks as $fallback)
                            <div class="p-stats__card u-stack-8" data-skeleton>
                                <span class="p-stats__value">{{ $fallback['value'] ?? __('cms::site.home.stats.default_value') }}</span>
                                <span>{{ $fallback['label'] ?? __('cms::site.home.stats.default_label') }}</span>
                            </div>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal skeletons">
            <div class="u-container u-stack-32">
                <div class="p-section__head u-stack-12">
                    <h2>{{ __('cms::site.home.products.heading') }}</h2>
                    <p>{{ __('cms::site.home.products.subheading') }}</p>
                </div>
                <div class="p-product-grid">
                    @forelse($featuredProducts as $product)
                        <article class="c-card" data-module="beacon" data-beacon-event="mini-product-view" data-beacon-payload="{{ $product['slug'] ?? '' }}">
                            <div class="c-card__media u-ratio-4x3">
                                @php $productCover = $product['cover_image'] ?? $placeholder('Product', 640, 480); @endphp
                                <img src="{{ $productCover }}" srcset="{{ $productCover }} 640w" sizes="(min-width: 62rem) 240px, 50vw"
                                     width="640" height="480" alt="{{ $product['name'] ?? 'Product' }}" loading="lazy">
                            </div>
                            <div class="u-stack-8">
                                <h3 class="c-card__title">{{ $product['name'] ?? $productFallbacks['title'] ?? 'Product' }}</h3>
                                <p class="c-card__meta">{{ $product['short_desc'] ?? $productFallbacks['description'] ?? '' }}</p>
                            </div>
                            <a class="c-button c-button--outline" href="{{ $product['slug'] ? route($pageLocale === 'en' ? 'cms.en.product.show' : 'cms.product.show', $product['slug']) : '#' }}">
                                {{ __('cms::site.home.products.cta') }}
                            </a>
                        </article>
                    @empty
                        @foreach((array) $productFallbacks as $fallback)
                            <article class="c-card u-stack-12" data-skeleton>
                                <div class="c-card__media u-ratio-4x3" aria-hidden="true"></div>
                                <div class="u-stack-8">
                                    <h3 class="c-card__title">{{ $fallback['title'] ?? 'Product' }}</h3>
                                    <p class="c-card__meta">{{ $fallback['description'] ?? '' }}</p>
                                </div>
                                <span class="c-button c-button--outline is-disabled">{{ __('cms::site.home.products.cta') }}</span>
                            </article>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal skeletons">
            <div class="u-container u-stack-32">
                <div class="p-section__head u-stack-12">
                    <h2>{{ __('cms::site.home.catalogs.heading') }}</h2>
                    <p>{{ __('cms::site.home.catalogs.subheading') }}</p>
                </div>
                <div class="p-catalog-grid">
                    @forelse($featuredCatalogs as $catalog)
                        <article class="c-card u-stack-12" data-module="beacon" data-beacon-event="mini-catalog-open" data-beacon-payload="{{ $catalog['title'] ?? '' }}">
                            <div class="c-card__media u-ratio-4x3">
                                @php $catalogCover = $catalog['cover'] ?? $placeholder('Catalog', 640, 480); @endphp
                                <img src="{{ $catalogCover }}" width="640" height="480" alt="{{ $catalog['title'] ?? 'Catalog' }}" loading="lazy">
                            </div>
                            <div class="u-stack-8">
                                <h3 class="c-card__title">{{ $catalog['title'] ?? $catalogFallbacks['title'] ?? 'Catalog' }}</h3>
                                <p class="c-card__meta">{{ $catalog['summary'] ?? $catalogFallbacks['description'] ?? '' }}</p>
                            </div>
                            @if(!empty($catalog['file']))
                                <a class="c-button c-button--outline" href="{{ $catalog['file'] }}" target="_blank" rel="noopener">
                                    {{ __('cms::site.home.catalogs.cta') }}
                                </a>
                            @else
                                <span class="c-button c-button--outline is-disabled">{{ __('cms::site.home.catalogs.cta') }}</span>
                            @endif
                        </article>
                    @empty
                        @foreach((array) $catalogFallbacks as $fallback)
                            <article class="c-card u-stack-12" data-skeleton>
                                <div class="c-card__media u-ratio-4x3" aria-hidden="true"></div>
                                <div class="u-stack-8">
                                    <h3 class="c-card__title">{{ $fallback['title'] ?? 'Catalog' }}</h3>
                                    <p class="c-card__meta">{{ $fallback['description'] ?? '' }}</p>
                                </div>
                                <span class="c-button c-button--outline is-disabled">{{ __('cms::site.home.catalogs.cta') }}</span>
                            </article>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-stack-32">
                <div class="p-section__head u-stack-12">
                    <h2>{{ __('cms::site.home.partners.heading') }}</h2>
                    <p>{{ __('cms::site.home.partners.subheading') }}</p>
                </div>
                <div class="p-partners">
                    @forelse($partners as $partner)
                        <div class="p-partners__item">
                            @if(!empty($partner['logo']))
                                <img src="{{ $partner['logo'] }}" width="200" height="120" alt="{{ $partner['title'] ?? 'Partner' }}" loading="lazy">
                            @else
                                <span>{{ $partner['title'] ?? 'Partner' }}</span>
                            @endif
                        </div>
                    @empty
                        @foreach(range(1, 6) as $index)
                            <div class="p-partners__item" data-skeleton>
                                <span aria-hidden="true">Logo {{ $index }}</span>
                            </div>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-stack-24">
                <div class="c-band u-align-center u-stack-16">
                    <h2>{{ $ctaBand['title'] ?? __('cms::site.home.cta.title') }}</h2>
                    <div class="u-cluster u-cluster--lg">
                        <a class="c-button c-button--primary" data-module="beacon" data-beacon-event="home-cta" data-beacon-payload="band"
                           href="{{ !empty($ctaBand['cta_link']) && !empty($ctaBand['cta_text']) ? $ctaBand['cta_link'] : ($pageLocale === 'en' ? route('cms.en.contact') : route('cms.contact')) }}">
                            {{ $ctaBand['cta_text'] ?? __('cms::site.home.cta.cta_text') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
