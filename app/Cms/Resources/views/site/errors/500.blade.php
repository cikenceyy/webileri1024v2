@php($assetKey = 'products')
@php($data = ['blocks' => []])
@extends('cms::site.layout')

@section('content')
    <section class="container py-5 text-center" data-analytics-section="error-500">
        <h1>500</h1>
        <p class="lead">{{ $locale === 'en' ? 'An unexpected error occurred.' : 'Beklenmeyen bir hata oluştu.' }}</p>
        <a href="{{ $locale === 'en' ? url('/en') : url('/') }}" class="btn btn-primary">{{ $locale === 'en' ? 'Go to homepage' : 'Anasayfaya dön' }}</a>
        <a href="{{ url('/admin') }}" class="btn btn-outline-secondary ms-2">{{ $locale === 'en' ? 'Back to dashboard' : "Panel'e dön" }}</a>
    </section>
@endsection
