@php($assetKey = 'catalogs')
@extends('cms::site.layout')

@section('content')
    <section class="container py-5" data-analytics-section="catalogs">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>{{ $locale === 'en' ? 'Catalogs' : 'Kataloglar' }}</h1>
        </div>
        <div class="row g-4">
            @foreach($catalogs as $catalog)
                <div class="col-md-4">
                    <div class="card h-100">
                        @if(!empty($catalog['cover']))
                            <img src="{{ $catalog['cover'] }}" class="card-img-top" alt="{{ $catalog['title'] ?? '' }}" loading="lazy" width="360" height="240">
                        @endif
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $catalog['title'] ?? '' }}</h5>
                            <a href="{{ $catalog['file'] ?? '#' }}" target="_blank" rel="noopener" class="btn btn-outline-primary mt-auto" data-analytics-click="catalog-open">{{ $locale === 'en' ? 'Open PDF' : 'PDF Aç' }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection
