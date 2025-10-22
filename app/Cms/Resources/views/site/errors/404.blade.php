@php($assetKey = 'products')
@php($data = ['blocks' => []])
@extends('cms::site.layout')

@section('content')
    <section class="container py-5 text-center" data-analytics-section="error-404">
        <h1>404</h1>
        <p class="lead">{{ $locale === 'en' ? 'The page you are looking for could not be found.' : 'Aradığınız sayfa bulunamadı.' }}</p>
        <a href="{{ $locale === 'en' ? url('/en') : url('/') }}" class="btn btn-primary">{{ $locale === 'en' ? 'Go to homepage' : 'Anasayfaya dön' }}</a>
    </section>
@endsection
