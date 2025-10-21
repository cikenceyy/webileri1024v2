<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-ui="theme" data-theme="soft-indigo" data-motion="soft">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ trim($__env->yieldContent('title', config('app.name', 'Webileri Admin'))) }}</title>

    @vite(['resources/scss/admin.scss', 'resources/js/admin.js'])
    @stack('page-styles')
    @stack('styles')
</head>
<body class="ui-body auth-body bg-light min-vh-100 d-flex flex-column">
    <main id="main-content" class="flex-grow-1 d-flex align-items-center justify-content-center py-5">
        @yield('content')
    </main>

    <x-ui-toast-stack />

    @stack('page-scripts')
    @stack('scripts')
</body>
</html>
