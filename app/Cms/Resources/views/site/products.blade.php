@extends('cms::site.layout', ['pageId' => 'products', 'locale' => $locale ?? app()->getLocale()])

@push('site-styles')
    @vite('app/Cms/Resources/assets/scss/site/products.scss')
@endpush

@push('site-scripts')
    @vite('app/Cms/Resources/assets/js/site/products.js')
@endpush

@section('content')
    @php
        $pageLocale = $locale ?? app()->getLocale();
        $placeholder = fn ($label, $width, $height) => 'data:image/svg+xml;utf8,' . rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$width} {$height}'><rect width='100%' height='100%' fill='%23e4e7ec'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='Inter, sans-serif' font-size='24' fill='%23667085'>{$label}</text></svg>");
        $hero = data_get($data, 'blocks.page_hero', data_get($data, 'blocks.hero', []));
        $filters = data_get($data, 'blocks.filters', []);
        $placeholders = \Illuminate\Support\Facades\Lang::get('cms::site.products.placeholders', [], $pageLocale);
    @endphp

    <div class="p-products">
        <section class="p-section p-section--hero" data-module="reveal">
            <div class="u-container u-container--wide u-stack-24">
                <div class="p-hero">
                    <div class="u-stack-16">
                        <h1>{{ $hero['title'] ?? __('cms::site.products.hero.title') }}</h1>
                        <p class="p-lead">{{ $hero['subtitle'] ?? __('cms::site.products.hero.subtitle') }}</p>
                    </div>
                </div>
            </div>
        </section>

        @if(!empty($filters))
            <section class="p-section" data-module="reveal">
                <div class="u-container u-stack-24">
                    <div class="u-cluster" role="tablist" aria-label="{{ __('cms::site.products.filters.label') }}">
                        <button type="button" class="c-chip is-active" data-filter="all">{{ __('cms::site.products.filters.all') }}</button>
                        @foreach($filters as $filter)
                            <button type="button" class="c-chip" data-filter="{{ $filter['slug'] ?? '' }}">{{ $filter['label'] ?? __('cms::site.products.filters.unknown') }}</button>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section class="p-section" data-module="reveal skeletons">
            <div class="u-container u-stack-32">
                <div class="p-product-grid" data-filter-target>
                    @forelse($products as $product)
                        <article class="c-card" data-category="{{ $product['category'] ?? 'all' }}">
                            <div class="c-card__media u-ratio-4x3">
                                @php $productCover = $product['cover_image'] ?? $placeholder('Product', 640, 480); @endphp
                                <img src="{{ $productCover }}" srcset="{{ $productCover }} 640w" sizes="(min-width: 62rem) 240px, 50vw"
                                     width="640" height="480" alt="{{ $product['name'] }}" loading="lazy">
                            </div>
                            <div class="u-stack-8">
                                <h2 class="c-card__title">{{ $product['name'] }}</h2>
                                <p class="c-card__meta">{{ $product['short_desc'] ?? __('cms::site.products.grid.description') }}</p>
                            </div>
                            @php
                                $productUrl = !empty($product['slug'])
                                    ? ($pageLocale === 'en' ? route('cms.en.product.show', $product['slug']) : route('cms.product.show', $product['slug']))
                                    : ($pageLocale === 'en' ? route('cms.en.products') : route('cms.products'));
                            @endphp
                            <a class="c-button c-button--outline" data-module="beacon" data-beacon-event="product-card-view" data-beacon-payload="{{ $product['slug'] ?? 'product' }}" href="{{ $productUrl }}">{{ __('cms::site.products.grid.cta') }}</a>
                        </article>
                    @empty
                        @foreach((array) $placeholders as $placeholderCard)
                            <article class="c-card u-stack-12" data-skeleton>
                                <div class="c-card__media u-ratio-4x3" aria-hidden="true"></div>
                                <div class="u-stack-8">
                                    <h2 class="c-card__title">{{ $placeholderCard['title'] ?? __('cms::site.products.grid.placeholder_title') }}</h2>
                                    <p class="c-card__meta">{{ $placeholderCard['description'] ?? __('cms::site.products.grid.placeholder_description') }}</p>
                                </div>
                                <span class="c-button c-button--outline is-disabled">{{ __('cms::site.products.grid.placeholder_cta') }}</span>
                            </article>
                        @endforeach
                    @endforelse
                </div>
                <div class="p-empty" data-empty-state hidden>
                    <p>{{ __('cms::site.products.grid.empty') }}</p>
                </div>
            </div>
        </section>
    </div>
@endsection
