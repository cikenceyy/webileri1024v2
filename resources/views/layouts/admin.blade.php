{{--
    Amaç: Admin yerleşimini TR dilinde tutarken runtime bootstrap bağını korumak.
    İlişkiler: PROMPT-4 — Layout & Runtime Bağı.
    Notlar: data-module ve data-page öznitelikleri admin.js ile eşleştirildi.
--}}
@php
    $moduleName = trim($__env->yieldContent('module', ''));
    $pageName = trim($__env->yieldContent('page', ''));
    $moduleHandle = trim($__env->yieldContent('module-handle', ''));
    $moduleSlug = trim($__env->yieldContent('module-slug', ''));

    if ($moduleHandle === '' && $moduleName !== '') {
        $moduleHandle = $moduleName;
    }

    if ($moduleHandle !== '') {
        $moduleHandle = \Illuminate\Support\Str::studly($moduleHandle);
    }

    if ($moduleSlug === '' && $moduleHandle !== '') {
        $moduleSlug = \Illuminate\Support\Str::slug($moduleHandle);
    }

    $moduleEntryPath = ($moduleHandle !== '' && $moduleSlug !== '')
        ? base_path("app/Modules/{$moduleHandle}/Resources/js/{$moduleSlug}.js")
        : '';

    $hasModuleEntry = $moduleEntryPath !== '' && is_file($moduleEntryPath);

    if (! $hasModuleEntry) {
        $moduleHandle = '';
        $moduleSlug = '';
    }
@endphp

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

            <div
                class="ui-layout__main layout-main"
                data-ui="page-shell"
                @if($moduleName !== '') data-module="{{ $moduleName }}" @endif
                @if($moduleHandle !== '') data-module-handle="{{ $moduleHandle }}" @endif
                @if($moduleSlug !== '') data-module-slug="{{ $moduleSlug }}" @endif
                @if($pageName !== '') data-page="{{ $pageName }}" @endif
            >

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
