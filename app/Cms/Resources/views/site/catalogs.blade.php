@extends('cms::site.layout', ['pageId' => 'catalogs', 'locale' => $locale ?? app()->getLocale()])

@push('site-styles')
    @vite('app/Cms/Resources/assets/scss/site/catalogs.scss')
@endpush

@push('site-scripts')
    @vite('app/Cms/Resources/assets/js/site/catalogs.js')
@endpush

@section('content')
    @php
        $pageLocale = $locale ?? app()->getLocale();
        $hero = data_get($data, 'blocks.page_hero', data_get($data, 'blocks.hero', []));
        $catalogEntries = $catalogs ?? data_get($data, 'blocks.list', []);
        $filters = data_get($data, 'blocks.filters_year', []);
        $placeholder = fn ($label, $width, $height) => 'data:image/svg+xml;utf8,' . rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$width} {$height}'><rect width='100%' height='100%' fill='%23e4e7ec'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='Inter, sans-serif' font-size='24' fill='%23667085'>{$label}</text></svg>");
        $placeholders = \Illuminate\Support\Facades\Lang::get('cms::site.catalogs.placeholders', [], $pageLocale);
    @endphp
    <section class="pattern-hero catalogs-hero" data-module="reveal">
        <div class="stack-lg">
            <h1>{{ $hero['title'] ?? __('cms::site.catalogs.hero.title') }}</h1>
            <p class="lead">{{ $hero['subtitle'] ?? __('cms::site.catalogs.hero.subtitle') }}</p>
        </div>
    </section>

    @if(!empty($filters))
        <section class="pattern-filters" data-module="reveal">
            <div class="filters cluster" role="tablist" aria-label="{{ __('cms::site.catalogs.filters.label') }}">
                <button type="button" class="filter-chip is-active" data-filter="all">{{ __('cms::site.catalogs.filters.all') }}</button>
                @foreach($filters as $filter)
                    <button type="button" class="filter-chip" data-filter="{{ $filter['slug'] ?? '' }}">{{ $filter['label'] ?? __('cms::site.catalogs.filters.unknown') }}</button>
                @endforeach
            </div>
        </section>
    @endif

    <section class="pattern-catalog-grid" data-module="reveal skeletons">
        <div class="catalog-grid grid-auto" data-filter-target>
            @forelse($catalogEntries as $catalog)
                <article class="catalog-card stack-sm" data-year="{{ strtolower($catalog['year'] ?? 'all') }}">
                    <div class="catalog-card__media ratio-4x3">
                        @php $catalogCover = $catalog['cover'] ?? $placeholder('Catalog', 640, 480); @endphp
                        <img src="{{ $catalogCover }}" srcset="{{ $catalogCover }} 640w" sizes="(min-width: 62rem) 240px, 50vw" width="640" height="480" alt="{{ $catalog['title'] ?? 'Catalog' }}" loading="lazy">
                    </div>
                    <div class="stack-xs">
                        <h2>{{ $catalog['title'] ?? __('cms::site.catalogs.card.title') }}</h2>
                        @if(!empty($catalog['year']))
                            <span class="catalog-meta">{{ $catalog['year'] }}</span>
                        @endif
                        @if(!empty($catalog['file']))
                            <a class="btn btn-outline" data-module="beacon" data-beacon-event="catalog_open" data-beacon-payload="catalogs" href="{{ $catalog['file'] }}" target="_blank" rel="noopener">{{ __('cms::site.catalogs.card.cta') }}</a>
                        @else
                            <span class="btn btn-outline is-disabled">{{ __('cms::site.catalogs.card.placeholder_cta') }}</span>
                        @endif
                    </div>
                </article>
            @empty
                @foreach((array) $placeholders as $placeholderCard)
                    <article class="catalog-card stack-sm placeholder" data-skeleton>
                        <div class="catalog-card__media ratio-4x3 placeholder-block"></div>
                        <div class="stack-xs">
                            <h2>{{ $placeholderCard['title'] ?? __('cms::site.catalogs.card.title') }}</h2>
                            <span class="btn btn-outline is-disabled">{{ __('cms::site.catalogs.card.placeholder_cta') }}</span>
                        </div>
                    </article>
                @endforeach
            @endforelse
        </div>
        <div class="catalog-empty" data-empty-state hidden>
            <p>{{ __('cms::site.catalogs.card.empty') }}</p>
        </div>
    </section>
@endsection
