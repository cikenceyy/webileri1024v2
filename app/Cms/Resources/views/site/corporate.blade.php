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
        $hero = data_get($data, 'blocks.hero', []);
        $intro = data_get($data, 'blocks.intro', []);
        $placeholder = fn ($label, $width, $height) => 'data:image/svg+xml;utf8,' . rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$width} {$height}'><rect width='100%' height='100%' fill='%23e4e7ec'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='Inter, sans-serif' font-size='24' fill='%23667085'>{$label}</text></svg>");
        $heroImage = $hero['image'] ?? $placeholder('Corporate', 1280, 720);
        $introImage = $intro['image'] ?? $placeholder('Team', 960, 640);
        $introPoints = \Illuminate\Support\Facades\Lang::get('cms::site.corporate.intro.points', [], $pageLocale);
    @endphp
    <section class="pattern-hero corporate-hero" data-module="reveal">
        <div class="stack-lg">
            <h1>{{ $hero['title'] ?? __('cms::site.corporate.hero.title') }}</h1>
            <p class="lead">{{ $hero['subtitle'] ?? __('cms::site.corporate.hero.subtitle') }}</p>
            @if(!empty($hero['cta_link']) && !empty($hero['cta_text']))
            <a class="btn btn-primary" data-module="beacon" data-beacon-event="cta" data-beacon-payload="corporate.hero" href="{{ $hero['cta_link'] }}">{{ $hero['cta_text'] }}</a>
            @endif
        </div>
        <div class="media-frame ratio-16x9">
            <img src="{{ $heroImage }}" srcset="{{ $heroImage }} 1280w, {{ $heroImage }} 960w" sizes="(min-width: 62rem) 50vw, 100vw" width="1280" height="720" alt="{{ $hero['title'] ?? 'Corporate hero' }}" loading="eager">
        </div>
    </section>

    <section class="pattern-split" data-module="reveal">
        <div class="split-grid">
            <div class="stack-md">
                <h2>{{ $intro['title'] ?? __('cms::site.corporate.intro.title') }}</h2>
                <p>{{ $intro['text'] ?? __('cms::site.corporate.intro.text') }}</p>
                <ul class="stack-xs">
                    @foreach((array) $introPoints as $point)
                        <li>{{ $point }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="media-frame ratio-3x2">
                <img src="{{ $introImage }}" srcset="{{ $introImage }} 960w, {{ $introImage }} 640w" sizes="(min-width: 62rem) 480px, 100vw" width="960" height="640" alt="{{ $intro['title'] ?? 'Corporate insight' }}" loading="lazy">
            </div>
        </div>
    </section>
@endsection
