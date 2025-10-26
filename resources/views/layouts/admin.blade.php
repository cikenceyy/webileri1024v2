<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-ui="theme" data-theme="bluewave" data-motion="soft">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @stack('meta')
        <title>{{ trim($__env->yieldContent('title', config('app.name', 'Webileri Admin'))) }}</title>

        @vite([
            'resources/scss/admin.scss',
            'resources/js/admin.js',
            'resources/css/tablekit.css',
            'resources/js/tablekit/index.js',
        ])
        @stack('page-styles')
        @stack('styles')
    </head>

    <body class="ui-body" data-ui="layout">
        @include('partials._navbar')

        <div class="ui-layout" data-ui="shell">
            @include('partials._sidebar')

            <div class="ui-layout__main">

                <x-ui-context>
                    @yield('content')
                </x-ui-context>

                @include('partials._footer')
            </div>
        </div>

        <x-ui-toast-stack />

        @stack('page-scripts')
        @stack('scripts')
    </body>
    
</html>
