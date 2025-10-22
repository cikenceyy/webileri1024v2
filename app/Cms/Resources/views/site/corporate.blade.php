@php($assetKey = 'corporate')
@extends('cms::site.layout')

@section('content')
    @php($blocks = $data['blocks'] ?? [])
    <section class="container py-5" data-analytics-section="corporate">
        <div class="row g-4 align-items-center">
            <div class="col-md-6">
                <h1>{{ $blocks['intro']['title'] ?? '' }}</h1>
                <p class="lead">{{ $blocks['intro']['text'] ?? '' }}</p>
            </div>
            <div class="col-md-6 text-center">
                @if(!empty($blocks['intro']['image']))
                    <img src="{{ $blocks['intro']['image'] }}" alt="" class="img-fluid" loading="lazy" width="540" height="360">
                @endif
            </div>
        </div>
    </section>
@endsection
