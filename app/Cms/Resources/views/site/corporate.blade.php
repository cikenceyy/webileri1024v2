@extends('cms::site.layout', ['pageId' => 'corporate', 'locale' => $locale ?? app()->getLocale()])

@push('site-styles')
    @vite('app/Cms/Resources/assets/scss/site/corporate.scss')
@endpush

@push('site-scripts')
    @vite('app/Cms/Resources/assets/js/site/corporate.js')
@endpush

@section('content')
    @php
        $pageLocale = $locale ?? app()->getLocale();
        $pageHero = data_get($data, 'blocks.page_hero', data_get($data, 'blocks.hero', []));
        $missionVision = data_get($data, 'blocks.mission_vision', []);
        $values = data_get($data, 'blocks.mission_values', []);
        $mediaLeft = data_get($data, 'blocks.media_left', data_get($data, 'blocks.intro', []));
        $capabilities = data_get($data, 'blocks.capabilities', []);
        $quality = data_get($data, 'blocks.quality_standards', []);
        $timeline = data_get($data, 'blocks.milestones_timeline', []);
        $partners = data_get($data, 'blocks.partners_band', []);
        $ctaBand = data_get($data, 'blocks.cta_band', []);
        $placeholder = fn ($label, $width, $height) => 'data:image/svg+xml;utf8,' . rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$width} {$height}'><rect width='100%' height='100%' fill='%23e4e7ec'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='Inter, sans-serif' font-size='24' fill='%23667085'>{$label}</text></svg>");
        $heroImage = $pageHero['image'] ?? $placeholder('Corporate', 1280, 720);
        $mediaImage = $mediaLeft['image'] ?? $placeholder('Team', 960, 640);
        $valueFallbacks = \Illuminate\Support\Facades\Lang::get('cms::site.corporate.values.defaults', [], $pageLocale);
        $capabilityFallbacks = \Illuminate\Support\Facades\Lang::get('cms::site.corporate.capabilities.defaults', [], $pageLocale);
        $qualityFallbacks = \Illuminate\Support\Facades\Lang::get('cms::site.corporate.quality.defaults', [], $pageLocale);
        $timelineFallbacks = \Illuminate\Support\Facades\Lang::get('cms::site.corporate.timeline.defaults', [], $pageLocale);
    @endphp

    <div class="corporate-page">
        <section class="p-section p-section--hero" data-module="reveal">
            <div class="u-container u-container--wide u-stack-32">
                <div class="p-hero">
                    <div class="u-stack-16">
                        <h1>{{ $pageHero['title'] ?? __('cms::site.corporate.page_hero.title') }}</h1>
                        <p class="p-lead">{{ $pageHero['intro'] ?? __('cms::site.corporate.page_hero.intro') }}</p>
                        @if(!empty($pageHero['cta_link']) && !empty($pageHero['cta_text']))
                            <a class="c-button c-button--primary" data-module="beacon" data-beacon-event="corporate-cta" data-beacon-payload="hero" href="{{ $pageHero['cta_link'] }}">{{ $pageHero['cta_text'] }}</a>
                        @endif
                    </div>
                    <div class="p-hero__media u-ratio-16x9" data-module="lazy-media">
                        <img src="{{ $heroImage }}" srcset="{{ $heroImage }} 1280w, {{ $heroImage }} 960w" sizes="(min-width: 62rem) 50vw, 100vw" width="1280" height="720" alt="{{ $pageHero['title'] ?? 'Corporate hero' }}" loading="eager">
                    </div>
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-stack-32">
                <div class="p-mission">
                    <article class="p-mission__card u-stack-12">
                        <h2>{{ __('cms::site.corporate.mission.heading') }}</h2>
                        <p>{{ $missionVision['mission'] ?? __('cms::site.corporate.mission.text') }}</p>
                    </article>
                    <article class="p-mission__card u-stack-12">
                        <h2>{{ __('cms::site.corporate.vision.heading') }}</h2>
                        <p>{{ $missionVision['vision'] ?? __('cms::site.corporate.vision.text') }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-stack-32">
                <div class="p-section__head u-stack-12">
                    <h2>{{ __('cms::site.corporate.values.heading') }}</h2>
                    <p>{{ __('cms::site.corporate.values.subheading') }}</p>
                </div>
                <div class="p-values">
                    @forelse($values as $value)
                        <article class="c-card u-stack-12">
                            <h3>{{ $value['title'] ?? __('cms::site.corporate.values.default_title') }}</h3>
                            <p class="u-text-secondary">{{ $value['description'] ?? __('cms::site.corporate.values.default_description') }}</p>
                        </article>
                    @empty
                        @foreach((array) $valueFallbacks as $fallback)
                            <article class="c-card u-stack-12" data-skeleton>
                                <h3>{{ $fallback['title'] ?? __('cms::site.corporate.values.default_title') }}</h3>
                                <p class="u-text-secondary">{{ $fallback['description'] ?? __('cms::site.corporate.values.default_description') }}</p>
                            </article>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-container--wide u-stack-32">
                <div class="p-media u-grid-two">
                    <div class="u-stack-16">
                        <h2>{{ $mediaLeft['title'] ?? __('cms::site.corporate.media_left.title') }}</h2>
                        <p>{{ $mediaLeft['text'] ?? __('cms::site.corporate.media_left.text') }}</p>
                    </div>
                    <div class="u-ratio-4x3" data-module="lazy-media">
                        <img src="{{ $mediaImage }}" srcset="{{ $mediaImage }} 960w, {{ $mediaImage }} 640w" sizes="(min-width: 62rem) 480px, 100vw" width="960" height="720" alt="{{ $mediaLeft['title'] ?? 'Corporate insight' }}" loading="lazy">
                    </div>
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-stack-32">
                <div class="p-section__head u-stack-12">
                    <h2>{{ __('cms::site.corporate.capabilities.heading') }}</h2>
                    <p>{{ __('cms::site.corporate.capabilities.subheading') }}</p>
                </div>
                <div class="p-capabilities">
                    @forelse($capabilities as $capability)
                        <article class="c-card u-stack-12">
                            @if(!empty($capability['icon']))
                                <img src="{{ $capability['icon'] }}" width="56" height="56" alt="" loading="lazy">
                            @endif
                            <h3>{{ $capability['title'] ?? __('cms::site.corporate.capabilities.default_title') }}</h3>
                            <p class="u-text-secondary">{{ $capability['description'] ?? __('cms::site.corporate.capabilities.default_description') }}</p>
                        </article>
                    @empty
                        @foreach((array) $capabilityFallbacks as $fallback)
                            <article class="c-card u-stack-12" data-skeleton>
                                <h3>{{ $fallback['title'] ?? __('cms::site.corporate.capabilities.default_title') }}</h3>
                                <p class="u-text-secondary">{{ $fallback['description'] ?? __('cms::site.corporate.capabilities.default_description') }}</p>
                            </article>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-stack-32">
                <div class="p-section__head u-stack-12">
                    <h2>{{ __('cms::site.corporate.quality.heading') }}</h2>
                    <p>{{ __('cms::site.corporate.quality.subheading') }}</p>
                </div>
                <div class="p-quality">
                    @forelse($quality as $certificate)
                        <article class="c-card u-stack-12">
                            <header class="u-stack-8">
                                <h3>{{ $certificate['name'] ?? __('cms::site.corporate.quality.default_name') }}</h3>
                                <span class="c-badge">{{ $certificate['code'] ?? __('cms::site.corporate.quality.default_code') }}</span>
                            </header>
                            <p class="u-text-secondary">{{ $certificate['text'] ?? __('cms::site.corporate.quality.default_text') }}</p>
                            @if(!empty($certificate['image']))
                                <div class="u-ratio-1x1" data-module="lazy-media">
                                    <img src="{{ $certificate['image'] }}" width="320" height="320" alt="{{ $certificate['name'] ?? 'Certificate' }}" loading="lazy">
                                </div>
                            @endif
                        </article>
                    @empty
                        @foreach((array) $qualityFallbacks as $fallback)
                            <article class="c-card u-stack-12" data-skeleton>
                                <header class="u-stack-8">
                                    <h3>{{ $fallback['name'] ?? __('cms::site.corporate.quality.default_name') }}</h3>
                                    <span class="c-badge">{{ $fallback['code'] ?? __('cms::site.corporate.quality.default_code') }}</span>
                                </header>
                                <p class="u-text-secondary">{{ $fallback['text'] ?? __('cms::site.corporate.quality.default_text') }}</p>
                            </article>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-container--wide u-stack-32">
                <div class="p-section__head u-stack-12">
                    <h2>{{ __('cms::site.corporate.timeline.heading') }}</h2>
                    <p>{{ __('cms::site.corporate.timeline.subheading') }}</p>
                </div>
                <div class="p-timeline" data-module="timeline-observer">
                    @forelse($timeline as $event)
                        <article class="p-timeline__item u-stack-8">
                            <span class="p-timeline__year">{{ $event['year'] ?? __('cms::site.corporate.timeline.default_year') }}</span>
                            <h3>{{ $event['label'] ?? __('cms::site.corporate.timeline.default_label') }}</h3>
                            <p class="u-text-secondary">{{ $event['description'] ?? __('cms::site.corporate.timeline.default_description') }}</p>
                        </article>
                    @empty
                        @foreach((array) $timelineFallbacks as $fallback)
                            <article class="p-timeline__item u-stack-8" data-skeleton>
                                <span class="p-timeline__year">{{ $fallback['year'] ?? __('cms::site.corporate.timeline.default_year') }}</span>
                                <h3>{{ $fallback['label'] ?? __('cms::site.corporate.timeline.default_label') }}</h3>
                                <p class="u-text-secondary">{{ $fallback['description'] ?? __('cms::site.corporate.timeline.default_description') }}</p>
                            </article>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-stack-32">
                <div class="p-section__head u-stack-12">
                    <h2>{{ __('cms::site.corporate.partners.heading') }}</h2>
                    <p>{{ __('cms::site.corporate.partners.subheading') }}</p>
                </div>
                <div class="p-partners">
                    @forelse($partners as $partner)
                        @php $logo = $partner['logo'] ?? null; @endphp
                        <a class="p-partners__item" @if(!empty($partner['link'])) href="{{ $partner['link'] }}" target="_blank" rel="noopener" @else aria-disabled="true" @endif>
                            @if($logo)
                                <img src="{{ $logo }}" width="160" height="80" alt="{{ $partner['name'] ?? __('cms::site.corporate.partners.placeholder') }}" loading="lazy">
                            @else
                                <span>{{ $partner['name'] ?? __('cms::site.corporate.partners.placeholder') }}</span>
                            @endif
                        </a>
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
                    <h2>{{ $ctaBand['title'] ?? __('cms::site.corporate.cta.title') }}</h2>
                    <p class="u-text-secondary">{{ __('cms::site.corporate.cta.subtitle') }}</p>
                    <a class="c-button c-button--primary" data-module="beacon" data-beacon-event="corporate-cta" data-beacon-payload="cta-band"
                       href="{{ $ctaBand['cta_link'] ?? ($pageLocale === 'en' ? route('cms.en.contact') : route('cms.contact')) }}">
                        {{ $ctaBand['cta_text'] ?? __('cms::site.corporate.cta.cta') }}
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
