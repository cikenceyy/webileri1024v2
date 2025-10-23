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
        $hero = data_get($data, 'blocks.hero', []);
        $placeholders = \Illuminate\Support\Facades\Lang::get('cms::site.products.placeholders', [], $pageLocale);
    @endphp

    <section class="pattern-hero products-hero" data-module="reveal">
        <div class="stack-lg">
            <h1>{{ $hero['title'] ?? __('cms::site.products.hero.title') }}</h1>
            <p class="lead">{{ $hero['subtitle'] ?? __('cms::site.products.hero.subtitle') }}</p>
        </div>
    </section>

    <section class="pattern-product-grid" data-module="reveal skeletons">
        <div class="product-grid grid-auto">
            @forelse($products as $product)
                <article class="product-card stack-sm">
                    <div class="product-card__media ratio-3x2">
                        @php $productCover = $product['cover_image'] ?? $placeholder('Product', 640, 480); @endphp
                        <img src="{{ $productCover }}" srcset="{{ $productCover }} 640w" sizes="(min-width: 62rem) 240px, 50vw" width="640" height="480" alt="{{ $product['name'] }}" loading="lazy">
                    </div>
                    <div class="stack-xs">
                        <h2>{{ $product['name'] }}</h2>
                        <p>{{ $product['short_desc'] ?? __('cms::site.products.grid.description') }}</p>
                        @php
                            $productUrl = !empty($product['slug'])
                                ? ($pageLocale === 'en' ? route('cms.en.product.show', $product['slug']) : route('cms.product.show', $product['slug']))
                                : ($pageLocale === 'en' ? route('cms.en.products') : route('cms.products'));
                        @endphp
                        <a class="btn btn-outline" href="{{ $productUrl }}">{{ __('cms::site.products.grid.cta') }}</a>
                    </div>
                </article>
            @empty
                @foreach((array) $placeholders as $placeholderCard)
                    <article class="product-card stack-sm placeholder" data-skeleton>
                        <div class="product-card__media ratio-3x2 placeholder-block"></div>
                        <div class="stack-xs">
                            <h2>{{ $placeholderCard['title'] ?? __('cms::site.products.grid.placeholder_title') }}</h2>
                            <p>{{ $placeholderCard['description'] ?? __('cms::site.products.grid.placeholder_description') }}</p>
                            <span class="btn btn-outline is-disabled">{{ __('cms::site.products.grid.placeholder_cta') }}</span>
                        </div>
                    </article>
                @endforeach
            @endforelse
        </div>
    </section>
@endsection
