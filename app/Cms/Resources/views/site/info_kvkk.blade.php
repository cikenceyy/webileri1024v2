@php($assetKey = 'contact')
@extends('cms::site.layout')

@section('content')
    @php($blocks = $data['blocks'] ?? [])
    <section class="container py-5" data-analytics-section="kvkk">
        <h1>{{ $blocks['body']['title'] ?? 'KVKK' }}</h1>
        <div class="mt-4 white-space-pre-wrap">{{ $blocks['body']['text'] ?? '' }}</div>
        @if(!empty($blocks['attachment']['file']))
            <a href="{{ $blocks['attachment']['file'] }}" class="btn btn-outline-primary mt-4" target="_blank" rel="noopener" data-analytics-click="kvkk-download">{{ $locale === 'en' ? 'Open attachment' : 'Ek Aç' }}</a>
        @endif
    </section>
@endsection
