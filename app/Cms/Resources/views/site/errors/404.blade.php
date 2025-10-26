@php($assetKey = 'products')
@php($data = ['blocks' => []])
@extends('cms::site.layout')

@section('content')
    <section class="p-section" data-module="reveal" data-analytics-section="error-404">
        <div class="u-container u-stack-24 u-text-center">
            <h1 class="p-display">404</h1>
            <p class="p-lead">{{ $locale === 'en' ? 'The page you are looking for could not be found.' : 'Aradığınız sayfa bulunamadı.' }}</p>
            <div class="u-cluster u-cluster--lg u-justify-center">
                <a href="{{ $locale === 'en' ? url('/en') : url('/') }}" class="c-button c-button--primary">{{ $locale === 'en' ? 'Go to homepage' : 'Anasayfaya dön' }}</a>
            </div>
        </div>
    </section>
@endsection
