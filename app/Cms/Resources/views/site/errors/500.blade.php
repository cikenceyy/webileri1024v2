@php($assetKey = 'products')
@php($data = ['blocks' => []])
@extends('cms::site.layout')

@section('content')
    <section class="p-section" data-module="reveal" data-analytics-section="error-500">
        <div class="u-container u-stack-24 u-text-center">
            <h1 class="p-display">500</h1>
            <p class="p-lead">{{ $locale === 'en' ? 'An unexpected error occurred.' : 'Beklenmeyen bir hata oluştu.' }}</p>
            <div class="u-cluster u-cluster--lg u-cluster--center">
                <a href="{{ $locale === 'en' ? url('/en') : url('/') }}" class="c-button c-button--primary">{{ $locale === 'en' ? 'Go to homepage' : 'Anasayfaya dön' }}</a>
                <a href="{{ url('/admin') }}" class="c-button c-button--outline">{{ $locale === 'en' ? 'Back to dashboard' : "Panel'e dön" }}</a>
            </div>
        </div>
    </section>
@endsection
