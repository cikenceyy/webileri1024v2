<!DOCTYPE html>
<html lang="{{ $locale ?? app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo['title'] ?? config('app.name') }}</title>
    @if(!empty($seo['description']))
        <meta name="description" content="{{ $seo['description'] }}">
    @endif
    <meta property="og:title" content="{{ $seo['title'] ?? config('app.name') }}">
    @if(!empty($seo['description']))
        <meta property="og:description" content="{{ $seo['description'] }}">
    @endif
    @if(!empty($seo['og_image']))
        <meta property="og:image" content="{{ $seo['og_image'] }}">
    @endif
    <meta property="og:type" content="website">

    @if(!empty($scripts['header']))
        {!! $scripts['header'] !!}
    @endif

    @yield('critical-css')

    @if(!empty($assetKey))
        @vite([
            "resources/scss/site/{$assetKey}.scss",
            "resources/js/site/{$assetKey}.js",
        ])
    @endif

    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('app.name'),
            'url' => url('/'),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    @php($contactBlock = data_get(($data ?? []), 'blocks.coords', []))
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => url('/'),
            'logo' => asset('logo.png'),
            'contactPoint' => [
                array_filter([
                    '@type' => 'ContactPoint',
                    'telephone' => $contactBlock['phone'] ?? null,
                    'contactType' => 'customer service',
                    'email' => $contactBlock['email'] ?? null,
                ]),
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    @stack('head')
</head>
<body data-locale="{{ $locale }}">
    @include('cms::site.partials.header')

    <main id="main" class="site-main">
        @yield('content')
    </main>

    @include('cms::site.partials.footer')

    @if(!empty($scripts['footer']))
        {!! $scripts['footer'] !!}
    @endif

    @stack('scripts')
</body>
</html>
