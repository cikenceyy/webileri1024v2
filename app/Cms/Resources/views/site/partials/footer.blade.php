@php
    $isEn = ($locale ?? app()->getLocale()) === 'en';
@endphp
<footer class="site-footer">
    <div class="container">
        <div class="site-footer__grid grid-auto">
            <div>
                <h2 class="site-footer__brand">{{ config('app.name') }}</h2>
                <p class="site-footer__text">{{ __('cms::site.footer.tagline') }}</p>
            </div>
            <div>
                <h3 class="site-footer__title">{{ __('cms::site.footer.explore') }}</h3>
                <ul class="site-footer__links stack-xs">
                    <li><a href="{{ $isEn ? route('cms.en.corporate') : route('cms.corporate') }}">{{ __('cms::site.navigation.corporate') }}</a></li>
                    <li><a href="{{ $isEn ? route('cms.en.products') : route('cms.products') }}">{{ __('cms::site.navigation.products') }}</a></li>
                    <li><a href="{{ $isEn ? route('cms.en.catalogs') : route('cms.catalogs') }}">{{ __('cms::site.navigation.catalogs') }}</a></li>
                    <li><a href="{{ $isEn ? route('cms.en.kvkk') : route('cms.kvkk') }}">{{ __('cms::site.navigation.kvkk') }}</a></li>
                </ul>
            </div>
            <div>
                <h3 class="site-footer__title">{{ __('cms::site.footer.contact') }}</h3>
                <address class="site-footer__text stack-xs">
                    <span>{{ __('cms::site.footer.address') }}</span>
                    <a href="tel:+902123334455">{{ __('cms::site.footer.phone') }}</a>
                    <a href="mailto:info@example.com">{{ __('cms::site.footer.email') }}</a>
                </address>
                <div class="site-footer__social cluster">
                    <a href="https://linkedin.com" aria-label="{{ __('cms::site.footer.linkedin') }}">in</a>
                    <a href="https://instagram.com" aria-label="{{ __('cms::site.footer.instagram') }}">ig</a>
                </div>
            </div>
        </div>
        <div class="site-footer__bottom">
            <small>© {{ date('Y') }} {{ config('app.name') }}. {{ __('cms::site.footer.rights') }}</small>
        </div>
    </div>
</footer>
