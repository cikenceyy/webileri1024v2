@php
    $isEn = ($locale ?? app()->getLocale()) === 'en';
    $routePrefix = $isEn ? 'cms.en.' : 'cms.';
    $navItems = [
        ['label' => $isEn ? 'Home' : 'Ana Sayfa', 'route' => $routePrefix . 'home'],
        ['label' => $isEn ? 'Corporate' : 'Kurumsal', 'route' => $routePrefix . 'corporate'],
        ['label' => $isEn ? 'Products' : 'Ürünler', 'route' => $routePrefix . 'products'],
        ['label' => $isEn ? 'Catalogs' : 'Kataloglar', 'route' => $routePrefix . 'catalogs'],
        ['label' => $isEn ? 'Contact' : 'İletişim', 'route' => $routePrefix . 'contact'],
    ];
    $switchRoute = $isEn
        ? route('cms.home')
        : route('cms.en.home');
@endphp
<header class="site-header" data-module="sticky-header">
    <div class="site-container">
        <div class="site-header__inner cluster">
            <a href="{{ $isEn ? route('cms.en.home') : route('cms.home') }}" class="site-logo">{{ config('app.name') }}</a>
            <button class="site-nav__toggle" type="button" data-module="nav-toggle" aria-expanded="false" aria-controls="primary-navigation">
                <span class="visually-hidden">{{ __('Toggle navigation') }}</span>
                <span class="site-nav__line"></span>
            </button>
            <nav id="primary-navigation" class="site-nav" aria-label="{{ __('Primary navigation') }}">
                <ul class="site-nav__list cluster" data-module="nav-list">
                    @foreach($navItems as $item)
                        <li class="site-nav__item">
                            <a href="{{ route($item['route']) }}" class="site-nav__link @if(Route::currentRouteName() === $item['route']) is-active @endif">{{ $item['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
            <div class="site-header__actions cluster">
                <a href="{{ $switchRoute }}" class="site-lang-switch" lang="{{ $isEn ? 'tr' : 'en' }}">{{ $isEn ? 'TR' : 'EN' }}</a>
            </div>
        </div>
    </div>
</header>
