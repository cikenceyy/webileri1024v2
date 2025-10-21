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
@php
    $moduleContext = trim($__env->yieldContent('module', $module ?? ''));
    $pageContext = trim($__env->yieldContent('page', $page ?? ''));
    $routeName = request()->route()?->getName();

    if ($moduleContext === '' && $routeName) {
        $map = [
            'admin.inventory.' => 'Inventory',
            'admin.crmsales.' => 'Marketing',
            'admin.marketing.' => 'Marketing',
            'admin.logistics.' => 'Logistics',
            'admin.finance.' => 'Finance',
        ];

        foreach ($map as $prefix => $name) {
            if (str_starts_with($routeName, $prefix)) {
                $moduleContext = $name;
                break;
            }
        }
    }

    $moduleSlug = $moduleContext !== '' ? strtolower($moduleContext) : '';
@endphp
<body class="ui-body" data-ui="layout" @if($moduleSlug !== '') data-module="{{ $moduleSlug }}" @endif>
    <header>
        @include('partials._navbar')
    </header>

    <div class="ui-layout" data-ui="shell">
        @include('partials._sidebar')

        <div class="ui-layout__main">
            <main
                id="main-content"
                class="layout-main"
                tabindex="-1"
                data-ui="content"
                @if($moduleContext !== '') data-module="{{ $moduleContext }}" @endif
                @if($moduleSlug !== '') data-module-slug="{{ $moduleSlug }}" @endif
                @if($pageContext !== '') data-page="{{ $pageContext }}" @endif
            >
                @yield('content')
            </main>

            @include('partials._footer')
        </div>
    </div>

    <x-ui.toast-stack />

    @stack('page-scripts')
    @stack('scripts')
</body>
</html>
