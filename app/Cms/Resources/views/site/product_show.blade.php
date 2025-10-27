@extends('cms::site.layout', ['pageId' => 'product-show', 'locale' => $locale ?? app()->getLocale()])

@push('site-styles')
    @vite('app/Cms/Resources/assets/scss/site/product-show.scss')
@endpush

@push('site-scripts')
    @vite('app/Cms/Resources/assets/js/site/product-show.js')
@endpush

@section('content')
    @php
        $pageLocale = $locale ?? app()->getLocale();
        $placeholder = fn ($label, $width, $height) => 'data:image/svg+xml;utf8,' . rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$width} {$height}'><rect width='100%' height='100%' fill='%23e4e7ec'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='Inter, sans-serif' font-size='24' fill='%23667085'>{$label}</text></svg>");
        $marketingBullets = data_get($data, 'blocks.marketing_bullets', []);
        $downloads = data_get($data, 'blocks.downloads', []);
        $related = $related ?? [];
    @endphp

    <div class="p-product-show">
        <section class="p-section p-section--hero" data-module="reveal">
            <div class="u-container u-container--wide u-stack-32">
                <div class="p-product-hero">
                    <div class="u-stack-16">
                        <a class="u-text-secondary" href="{{ $pageLocale === 'en' ? route('cms.en.products') : route('cms.products') }}">&larr; {{ __('cms::site.product_show.back') }}</a>
                        <h1>{{ $product['name'] }}</h1>
                        <p class="p-lead">{{ $product['short_desc'] ?? __('cms::site.product_show.description') }}</p>
                        <div class="p-meta">
                            <div class="c-card u-stack-8 u-border-subtle">
                                <span class="c-badge">{{ __('cms::site.product_show.sku') }}</span>
                                <strong>{{ $product['sku'] ?? '—' }}</strong>
                            </div>
                            <div class="c-card u-stack-8 u-border-subtle">
                                <span class="c-badge">{{ __('cms::site.product_show.price_label') }}</span>
                                <span>{{ __('cms::site.product_show.price_callout') }}</span>
                            </div>
                        </div>
                        <a class="c-button c-button--primary" data-module="beacon" data-beacon-event="product-contact" data-beacon-payload="{{ $product['slug'] ?? 'product' }}" href="{{ $pageLocale === 'en' ? route('cms.en.contact') : route('cms.contact') }}">{{ __('cms::site.product_show.cta') }}</a>
                    </div>
                    <div class="p-gallery" data-module="gallery-core">
                        <div class="u-ratio-16x9" data-gallery-main>
                            @php $cover = $product['cover_image'] ?? $placeholder('Product', 1280, 720); @endphp
                            <img src="{{ $cover }}" srcset="{{ $cover }} 1280w, {{ $cover }} 960w" sizes="(min-width: 62rem) 560px, 100vw" width="1280" height="720" alt="{{ $product['name'] }}" loading="eager">
                        </div>
                        @if(!empty($product['gallery']))
                            <div class="p-gallery__thumbs" role="tablist" aria-label="{{ __('cms::site.product_show.gallery') }}">
                                @foreach($product['gallery'] as $index => $media)
                                    @php $thumb = $media ?: $placeholder('Media', 320, 200); @endphp
                                    <button type="button" class="c-thumb @if($loop->first) is-active @endif" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-gallery-src="{{ $thumb }}">
                                        <img src="{{ $thumb }}" width="160" height="100" alt="{{ $product['name'] }} thumbnail {{ $index + 1 }}" loading="lazy">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-stack-32">
                <div class="p-section__head u-stack-12">
                    <h2>{{ __('cms::site.product_show.bullets.heading') }}</h2>
                    <p>{{ __('cms::site.product_show.bullets.subheading') }}</p>
                </div>
                <div class="p-marketing">
                    @forelse($marketingBullets as $bullet)
                        <article class="c-card u-stack-12">
                            <h3>{{ $bullet['title'] ?? __('cms::site.product_show.bullets.default_title') }}</h3>
                            <p class="u-text-secondary">{{ $bullet['description'] ?? __('cms::site.product_show.bullets.default_description') }}</p>
                        </article>
                    @empty
                        @foreach(\Illuminate\Support\Facades\Lang::get('cms::site.product_show.bullets.defaults', [], $pageLocale) as $fallback)
                            <article class="c-card u-stack-12" data-skeleton>
                                <h3>{{ $fallback['title'] ?? __('cms::site.product_show.bullets.default_title') }}</h3>
                                <p class="u-text-secondary">{{ $fallback['description'] ?? __('cms::site.product_show.bullets.default_description') }}</p>
                            </article>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-stack-32">
                <div class="p-section__head u-stack-12">
                    <h2>{{ __('cms::site.product_show.downloads.heading') }}</h2>
                    <p>{{ __('cms::site.product_show.downloads.subheading') }}</p>
                </div>
                <div class="p-downloads">
                    @forelse($downloads as $download)
                        <a class="c-card u-stack-8" data-module="beacon" data-beacon-event="product-download" data-beacon-payload="{{ $download['title'] ?? 'document' }}" href="{{ $download['file'] ?? '#' }}" @if(!empty($download['file'])) target="_blank" rel="noopener" @else aria-disabled="true" @endif>
                            <span class="c-badge" aria-hidden="true">PDF</span>
                            <span class="c-card__title">{{ $download['title'] ?? __('cms::site.product_show.downloads.placeholder') }}</span>
                        </a>
                    @empty
                        <span class="c-card u-stack-8" data-skeleton>{{ __('cms::site.product_show.downloads.placeholder') }}</span>
                    @endforelse
                </div>
            </div>
        </section>

        @if(!empty($related))
            <section class="p-section" data-module="reveal">
                <div class="u-container u-stack-32">
                    <div class="p-section__head u-stack-12">
                        <h2>{{ __('cms::site.product_show.related.heading') }}</h2>
                    </div>
                    <div class="p-related">
                        @foreach($related as $item)
                            <article class="c-card u-stack-12">
                                <div class="c-card__media u-ratio-4x3">
                                    @php $relatedCover = $item['cover_image'] ?? $placeholder('Product', 640, 480); @endphp
                                    <img src="{{ $relatedCover }}" width="640" height="480" alt="{{ $item['name'] ?? 'Product' }}" loading="lazy">
                                </div>
                                <div class="u-stack-8">
                                    <h3 class="c-card__title">{{ $item['name'] ?? 'Product' }}</h3>
                                    <p class="c-card__meta">{{ $item['short_desc'] ?? '' }}</p>
                                </div>
                                <a class="c-button c-button--outline" href="{{ $item['slug'] ? ($pageLocale === 'en' ? route('cms.en.product.show', $item['slug']) : route('cms.product.show', $item['slug'])) : '#' }}">
                                    {{ __('cms::site.products.grid.cta') }}
                                </a>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>
@endsection
