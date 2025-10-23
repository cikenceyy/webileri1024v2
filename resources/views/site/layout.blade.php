<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo['title'] ?? config('app.name') }}</title>
    @if(!empty($seo['description']))
        <meta name="description" content="{{ $seo['description'] }}">
        <meta property="og:description" content="{{ $seo['description'] }}">
    @endif
    @if(!empty($seo['title']))
        <meta property="og:title" content="{{ $seo['title'] }}">
    @endif
    @if(!empty($seo['og_image']))
        <meta property="og:image" content="{{ $seo['og_image'] }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @stack('critical-css')
    @stack('site-styles')
    @if(!empty($scripts['header']))
        {!! $scripts['header'] !!}
    @endif
</head>
<body data-page="{{ $pageId ?? '' }}" class="site-body">
    <a class="skip-link" href="#main">{{ __('Skip to content') }}</a>
    @include('site.partials.header', ['pageId' => $pageId ?? null, 'locale' => $locale ?? app()->getLocale()])
    <main id="main" class="site-main">
        @yield('content')
    </main>
    @include('site.partials.footer', ['locale' => $locale ?? app()->getLocale()])
    @stack('site-scripts')
    @if(!empty($scripts['footer']))
        {!! $scripts['footer'] !!}
    @endif
</body>
</html>
