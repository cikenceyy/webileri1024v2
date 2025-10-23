@extends('site.layout', ['pageId' => 'kvkk', 'locale' => $locale ?? app()->getLocale()])

@push('site-styles')
    @vite('resources/scss/site/kvkk.scss')
@endpush

@push('site-scripts')
    @vite('resources/js/site/kvkk.js')
@endpush

@section('content')
    @php
        $hero = data_get($data, 'blocks.hero', []);
        $body = data_get($data, 'blocks.body', []);
        $attachment = data_get($data, 'blocks.attachment.file');
    @endphp
    <section class="pattern-hero kvkk-hero" data-module="reveal">
        <div class="stack-lg">
            <h1>{{ $hero['title'] ?? ($locale === 'en' ? 'Privacy & KVKK' : 'KVKK Politikası') }}</h1>
            <p class="lead">{{ $hero['subtitle'] ?? ($locale === 'en' ? 'We protect personal data with transparent governance.' : 'Kişisel verileri şeffaf yönetişimle koruyoruz.') }}</p>
        </div>
    </section>

    <section class="pattern-article" data-module="reveal">
        <div class="stack-lg">
            <h2>{{ $body['title'] ?? ($locale === 'en' ? 'Processing principles' : 'İşleme prensipleri') }}</h2>
            <p>{{ $body['text'] ?? ($locale === 'en' ? 'Our KVKK policy outlines data lifecycle management, retention and deletion standards.' : 'KVKK politikamız veri yaşam döngüsü, saklama ve silme standartlarını ortaya koyar.') }}</p>
            @if($attachment)
                <a class="btn btn-outline" href="{{ $attachment }}" target="_blank" rel="noopener">{{ $locale === 'en' ? 'Open policy PDF' : 'Politikayı aç' }}</a>
            @else
                <span class="btn btn-outline is-disabled">{{ $locale === 'en' ? 'PDF coming soon' : 'PDF çok yakında' }}</span>
            @endif
        </div>
    </section>
@endsection
