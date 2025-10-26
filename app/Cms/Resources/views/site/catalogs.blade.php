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

    <div class="p-catalogs">
        <section class="p-section p-section--hero" data-module="reveal">
            <div class="u-container u-container--wide u-stack-24">
                <div class="p-hero">
                    <div class="u-stack-16">
                        <h1>{{ $hero['title'] ?? __('cms::site.catalogs.hero.title') }}</h1>
                        <p class="p-lead">{{ $hero['subtitle'] ?? __('cms::site.catalogs.hero.subtitle') }}</p>
                    </div>
                </div>
            </div>
        </section>

        @if(!empty($filters))
            <section class="p-section" data-module="reveal">
                <div class="u-container u-stack-24">
                    <div class="u-cluster" role="tablist" aria-label="{{ __('cms::site.catalogs.filters.label') }}">
                        <button type="button" class="c-chip is-active" data-filter="all">{{ __('cms::site.catalogs.filters.all') }}</button>
                        @foreach($filters as $filter)
                            <button type="button" class="c-chip" data-filter="{{ $filter['slug'] ?? '' }}">{{ $filter['label'] ?? __('cms::site.catalogs.filters.unknown') }}</button>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section class="p-section" data-module="reveal skeletons">
            <div class="u-container u-stack-32">
                <div class="p-catalog-grid" data-filter-target>
                    @forelse($catalogEntries as $catalog)
                        <article class="c-card u-stack-12" data-year="{{ strtolower($catalog['year'] ?? 'all') }}" data-module="beacon" data-beacon-event="catalog-open" data-beacon-payload="catalogs">
                            <div class="c-card__media u-ratio-4x3">
                                @php $catalogCover = $catalog['cover'] ?? $placeholder('Catalog', 640, 480); @endphp
                                <img src="{{ $catalogCover }}" srcset="{{ $catalogCover }} 640w" sizes="(min-width: 62rem) 240px, 50vw"
                                     width="640" height="480" alt="{{ $catalog['title'] ?? 'Catalog' }}" loading="lazy">
                            </div>
                            <div class="u-stack-8">
                                <h2 class="c-card__title">{{ $catalog['title'] ?? __('cms::site.catalogs.card.title') }}</h2>
                                @if(!empty($catalog['year']))
                                    <span class="c-card__meta">{{ $catalog['year'] }}</span>
                                @endif
                            </div>
                            @if(!empty($catalog['file']))
                                <a class="c-button c-button--outline" href="{{ $catalog['file'] }}" target="_blank" rel="noopener">{{ __('cms::site.catalogs.card.cta') }}</a>
                            @else
                                <span class="c-button c-button--outline is-disabled">{{ __('cms::site.catalogs.card.placeholder_cta') }}</span>
                            @endif
                        </article>
                    @empty
                        @foreach((array) $placeholders as $placeholderCard)
                            <article class="c-card u-stack-12" data-skeleton>
                                <div class="c-card__media u-ratio-4x3" aria-hidden="true"></div>
                                <div class="u-stack-8">
                                    <h2 class="c-card__title">{{ $placeholderCard['title'] ?? __('cms::site.catalogs.card.title') }}</h2>
                                </div>
                                <span class="c-button c-button--outline is-disabled">{{ __('cms::site.catalogs.card.placeholder_cta') }}</span>
                            </article>
                        @endforeach
                    @endforelse
                </div>
                <div class="p-empty" data-empty-state hidden>
                    <p>{{ __('cms::site.catalogs.card.empty') }}</p>
                </div>
            </div>
        </section>
    </div>
@endsection
