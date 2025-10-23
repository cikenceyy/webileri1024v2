@extends('site.layout', ['pageId' => 'corporate', 'locale' => $locale ?? app()->getLocale()])

@push('site-styles')
    @vite('resources/scss/site/corporate.scss')
@endpush

@push('site-scripts')
    @vite('resources/js/site/corporate.js')
@endpush

@section('content')
    @php
        $hero = data_get($data, 'blocks.hero', []);
        $intro = data_get($data, 'blocks.intro', []);
        $placeholder = fn ($label, $width, $height) => 'data:image/svg+xml;utf8,' . rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$width} {$height}'><rect width='100%' height='100%' fill='%23e4e7ec'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='Inter, sans-serif' font-size='24' fill='%23667085'>{$label}</text></svg>");
        $heroImage = $hero['image'] ?? $placeholder('Corporate', 1280, 720);
        $introImage = $intro['image'] ?? $placeholder('Team', 960, 640);
    @endphp
    <section class="pattern-hero corporate-hero" data-module="reveal">
        <div class="stack-lg">
            <h1>{{ $hero['title'] ?? ($locale === 'en' ? 'About our company' : 'Şirketimiz hakkında') }}</h1>
            <p class="lead">{{ $hero['subtitle'] ?? ($locale === 'en' ? 'We design resilient systems for demanding operations.' : 'Zorlu operasyonlar için dayanıklı sistemler tasarlıyoruz.') }}</p>
            @if(!empty($hero['cta_link']) && !empty($hero['cta_text']))
                <a class="btn btn-primary" href="{{ $hero['cta_link'] }}">{{ $hero['cta_text'] }}</a>
            @endif
        </div>
        <div class="media-frame ratio-16x9">
            <img src="{{ $heroImage }}" srcset="{{ $heroImage }} 1280w, {{ $heroImage }} 960w" sizes="(min-width: 62rem) 50vw, 100vw" width="1280" height="720" alt="{{ $hero['title'] ?? 'Corporate hero' }}" loading="eager">
        </div>
    </section>

    <section class="pattern-split" data-module="reveal">
        <div class="split-grid">
            <div class="stack-md">
                <h2>{{ $intro['title'] ?? ($locale === 'en' ? 'Human-centered engineering' : 'İnsan odaklı mühendislik') }}</h2>
                <p>{{ $intro['text'] ?? ($locale === 'en' ? 'From design to after sales, we combine automation expertise with hands-on partnership.' : 'Tasarım aşamasından satış sonrası desteğe kadar, otomasyon uzmanlığımızı yakın iş ortaklığı ile birleştiriyoruz.') }}</p>
                <ul class="stack-xs">
                    <li>{{ $locale === 'en' ? 'ISO-certified production' : 'ISO sertifikalı üretim' }}</li>
                    <li>{{ $locale === 'en' ? 'Dedicated project offices' : 'Adanmış proje ofisleri' }}</li>
                    <li>{{ $locale === 'en' ? 'Global service network' : 'Küresel servis ağı' }}</li>
                </ul>
            </div>
            <div class="media-frame ratio-3x2">
                <img src="{{ $introImage }}" srcset="{{ $introImage }} 960w, {{ $introImage }} 640w" sizes="(min-width: 62rem) 480px, 100vw" width="960" height="640" alt="{{ $intro['title'] ?? 'Corporate insight' }}" loading="lazy">
            </div>
        </div>
    </section>
@endsection
