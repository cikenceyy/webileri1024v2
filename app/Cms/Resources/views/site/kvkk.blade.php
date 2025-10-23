@extends('cms::site.layout', ['pageId' => 'kvkk', 'locale' => $locale ?? app()->getLocale()])

@push('site-styles')
    @vite('app/Cms/Resources/assets/scss/site/kvkk.scss')
@endpush

@push('site-scripts')
    @vite('app/Cms/Resources/assets/js/site/kvkk.js')
@endpush

@section('content')
    @php
        $pageLocale = $locale ?? app()->getLocale();
        $hero = data_get($data, 'blocks.hero', []);
        $body = data_get($data, 'blocks.body', []);
        $attachment = data_get($data, 'blocks.attachment.file');
    @endphp
    <section class="pattern-hero kvkk-hero" data-module="reveal">
        <div class="stack-lg">
            <h1>{{ $hero['title'] ?? __('cms::site.kvkk.hero.title') }}</h1>
            <p class="lead">{{ $hero['subtitle'] ?? __('cms::site.kvkk.hero.subtitle') }}</p>
        </div>
    </section>

    <section class="pattern-article" data-module="reveal">
        <div class="stack-lg">
            <h2>{{ $body['title'] ?? __('cms::site.kvkk.body.title') }}</h2>
            <p>{{ $body['text'] ?? __('cms::site.kvkk.body.text') }}</p>
            @if($attachment)
                <a class="btn btn-outline" data-module="beacon" data-beacon-event="kvkk.pdf" href="{{ $attachment }}" target="_blank" rel="noopener">{{ __('cms::site.kvkk.body.cta') }}</a>
            @else
                <span class="btn btn-outline is-disabled">{{ __('cms::site.kvkk.body.placeholder_cta') }}</span>
            @endif
        </div>
    </section>
@endsection
