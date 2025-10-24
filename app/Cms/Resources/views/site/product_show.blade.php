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
    @endphp

    <section class="section section--hero" data-module="reveal">
        <div class="container container--wide">
            <div class="pattern-product-hero product-hero grid">
                <div class="stack-lg">
                    <a class="back-link" href="{{ $pageLocale === 'en' ? route('cms.en.products') : route('cms.products') }}">&larr; {{ __('cms::site.product_show.back') }}</a>
                    <h1>{{ $product['name'] }}</h1>
                    <p class="lead">{{ $product['short_desc'] ?? __('cms::site.product_show.description') }}</p>
                    <ul class="product-meta stack-xs">
                        <li><strong>{{ __('cms::site.product_show.sku') }}:</strong> {{ $product['sku'] ?? '—' }}</li>
                        <li>{{ __('cms::site.product_show.price_callout') }}</li>
                    </ul>
                    <a class="btn btn-primary" data-module="beacon" data-beacon-event="product-contact" data-beacon-payload="{{ $product['slug'] ?? 'product' }}" href="{{ $pageLocale === 'en' ? route('cms.en.contact') : route('cms.contact') }}">{{ __('cms::site.product_show.cta') }}</a>
                </div>
                <div class="product-gallery" data-module="light-gallery">
                    <div class="gallery-main ratio-16x9">
                        @php $cover = $product['cover_image'] ?? $placeholder('Product', 1280, 720); @endphp
                        <img src="{{ $cover }}" srcset="{{ $cover }} 1280w, {{ $cover }} 960w" sizes="(min-width: 62rem) 560px, 100vw" width="1280" height="720" alt="{{ $product['name'] }}" loading="eager">
                    </div>
                    @if(!empty($product['gallery']))
                        <div class="gallery-thumbs" role="tablist" aria-label="{{ __('cms::site.product_show.gallery') }}">
                            @foreach($product['gallery'] as $index => $media)
                                @php $thumb = $media ?: $placeholder('Media', 320, 200); @endphp
                                <button type="button" class="thumb" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-gallery-src="{{ $thumb }}">
                                    <img src="{{ $thumb }}" width="160" height="100" alt="{{ $product['name'] }} thumbnail {{ $index + 1 }}" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="section" data-module="reveal">
        <div class="container">
            <div class="pattern-bullets">
                <div class="section-head stack-sm">
                    <h2>{{ __('cms::site.product_show.bullets.heading') }}</h2>
                    <p>{{ __('cms::site.product_show.bullets.subheading') }}</p>
                </div>
                <div class="bullet-grid">
                    @forelse($marketingBullets as $bullet)
                        <article class="bullet-card stack-sm">
                            <h3>{{ $bullet['title'] ?? __('cms::site.product_show.bullets.default_title') }}</h3>
                            <p>{{ $bullet['description'] ?? __('cms::site.product_show.bullets.default_description') }}</p>
                        </article>
                    @empty
                        @foreach(\Illuminate\Support\Facades\Lang::get('cms::site.product_show.bullets.defaults', [], $pageLocale) as $fallback)
                            <article class="bullet-card stack-sm" data-skeleton>
                                <h3>{{ $fallback['title'] ?? __('cms::site.product_show.bullets.default_title') }}</h3>
                                <p>{{ $fallback['description'] ?? __('cms::site.product_show.bullets.default_description') }}</p>
                            </article>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="section" data-module="reveal">
        <div class="container">
            <div class="pattern-downloads">
                <div class="section-head stack-sm">
                    <h2>{{ __('cms::site.product_show.downloads.heading') }}</h2>
                    <p>{{ __('cms::site.product_show.downloads.subheading') }}</p>
                </div>
                <div class="downloads-grid">
                    @forelse($downloads as $download)
                        <a class="download-card stack-xs" data-module="beacon" data-beacon-event="product-download" data-beacon-payload="{{ $download['title'] ?? 'document' }}" href="{{ $download['file'] ?? '#' }}" @if(!empty($download['file'])) target="_blank" rel="noopener" @else aria-disabled="true" @endif>
                            <span class="download-card__icon" aria-hidden="true">⬇︎</span>
                            <span class="download-card__title">{{ $download['title'] ?? __('cms::site.product_show.downloads.placeholder') }}</span>
                        </a>
                    @empty
                        <span class="download-card placeholder" data-skeleton>{{ __('cms::site.product_show.downloads.placeholder') }}</span>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection
