@php
    $isEn = ($locale ?? app()->getLocale()) === 'en';
    $routePrefix = $isEn ? 'cms.en.' : 'cms.';
    $navItems = [
        ['label' => __('cms::site.navigation.home'), 'route' => $routePrefix . 'home'],
        ['label' => __('cms::site.navigation.corporate'), 'route' => $routePrefix . 'corporate'],
        ['label' => __('cms::site.navigation.products'), 'route' => $routePrefix . 'products'],
        ['label' => __('cms::site.navigation.catalogs'), 'route' => $routePrefix . 'catalogs'],
        ['label' => __('cms::site.navigation.contact'), 'route' => $routePrefix . 'contact'],
        ['label' => __('cms::site.navigation.kvkk'), 'route' => $routePrefix . 'kvkk'],
    ];
    $switchRoute = $isEn ? route('cms.home') : route('cms.en.home');
    $contactRoute = $isEn ? route('cms.en.contact') : route('cms.contact');
@endphp
<header class="c-site-header" data-module="sticky-header">
    <div class="u-container">
        <div class="c-site-header__inner">
            <a href="{{ $isEn ? route('cms.en.home') : route('cms.home') }}" class="c-site-logo">{{ config('app.name') }}</a>
            <button class="c-site-nav__toggle" type="button" data-module="nav-toggle" aria-expanded="false" aria-controls="primary-navigation">
                <span class="u-visually-hidden">{{ __('cms::site.navigation.toggle') }}</span>
                <span class="c-site-nav__line"></span>
            </button>
            <nav id="primary-navigation" class="c-site-nav" aria-label="{{ __('cms::site.navigation.primary') }}">
                <ul class="c-site-nav__list" data-module="nav-list">
                    @foreach($navItems as $item)
                        <li class="c-site-nav__item">
                            <a href="{{ route($item['route']) }}" class="c-site-nav__link @if(Route::currentRouteName() === $item['route']) is-active @endif">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
            <div class="c-site-header__actions">
                <a href="{{ $switchRoute }}" class="c-site-lang-switch" lang="{{ $isEn ? 'tr' : 'en' }}">
                    {{ $isEn ? __('cms::site.navigation.lang_tr') : __('cms::site.navigation.lang_en') }}
                </a>
                <a href="{{ $contactRoute }}" class="c-button c-button--primary" data-module="beacon" data-beacon-event="header-cta">
                    {{ __('cms::site.navigation.contact_cta') }}
                </a>
            </div>
        </div>
    </div>
</header>
