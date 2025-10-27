@php($assetKey = 'contact')
@extends('cms::site.layout')

@section('content')
    @php($blocks = $data['blocks'] ?? [])
    <section class="p-section" data-analytics-section="kvkk">
        <div class="u-container u-container--narrow u-stack-24">
            <h1 class="p-display">{{ $blocks['body']['title'] ?? 'KVKK' }}</h1>
            <div class="p-richtext">{!! nl2br(e($blocks['body']['text'] ?? '')) !!}</div>
            @if(!empty($blocks['attachment']['file']))
                <a href="{{ $blocks['attachment']['file'] }}" class="c-button c-button--outline" target="_blank" rel="noopener" data-analytics-click="kvkk-download">{{ $locale === 'en' ? 'Open attachment' : 'Ek Aç' }}</a>
            @endif
        </div>
    </section>
@endsection
