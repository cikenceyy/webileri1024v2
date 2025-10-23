@extends('layouts.admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-4 gap-3">
            <div>
                <h1 class="h3 mb-1">{{ __('Content Pages') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage public content blocks, SEO meta and script injections per page.') }}</p>
            </div>
            <ul class="nav nav-pills flex-wrap gap-2" role="tablist">
                @foreach($pages as $key => $page)
                    <li class="nav-item">
                        <a class="nav-link @if($loop->first) active @endif" href="{{ route('cms.admin.pages.edit', $key) }}">
                            {{ $page['label'] ?? ucfirst($key) }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-4">
                    @foreach($pages as $key => $page)
                        <div class="col-md-6 col-xl-4">
                            <div class="border rounded p-4 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h2 class="h5 mb-1">{{ $page['label'] ?? ucfirst($key) }}</h2>
                                        <p class="text-muted small mb-0">/{{ $key }}</p>
                                    </div>
                                    <span class="badge bg-light text-dark">{{ strtoupper($key) }}</span>
                                </div>
                                <p class="text-muted small mb-4">{{ $page['description'] ?? __('Page level content blocks, SEO, scripts and media assets.') }}</p>
                                <a href="{{ route('cms.admin.pages.edit', $key) }}" class="btn btn-primary btn-sm">{{ __('Manage page') }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
