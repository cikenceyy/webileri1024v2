@php
    $isEn = ($locale ?? app()->getLocale()) === 'en';
@endphp
<footer class="c-site-footer">
    <div class="u-container">
        <div class="c-site-footer__grid">
            <div class="u-stack-16">
                <h2 class="c-site-footer__brand">{{ config('app.name') }}</h2>
                <p class="c-site-footer__text">{{ __('cms::site.footer.tagline') }}</p>
            </div>
            <div class="u-stack-16">
                <h3 class="c-site-footer__title">{{ __('cms::site.footer.explore') }}</h3>
                <ul class="c-site-footer__links u-stack-12">
                    <li><a href="{{ $isEn ? route('cms.en.corporate') : route('cms.corporate') }}">{{ __('cms::site.navigation.corporate') }}</a></li>
                    <li><a href="{{ $isEn ? route('cms.en.products') : route('cms.products') }}">{{ __('cms::site.navigation.products') }}</a></li>
                    <li><a href="{{ $isEn ? route('cms.en.catalogs') : route('cms.catalogs') }}">{{ __('cms::site.navigation.catalogs') }}</a></li>
                    <li><a href="{{ $isEn ? route('cms.en.kvkk') : route('cms.kvkk') }}">{{ __('cms::site.navigation.kvkk') }}</a></li>
                </ul>
            </div>
            <div class="u-stack-16">
                <h3 class="c-site-footer__title">{{ __('cms::site.footer.contact') }}</h3>
                <address class="c-site-footer__text u-stack-12">
                    <span>{{ __('cms::site.footer.address') }}</span>
                    <a href="tel:+902123334455">{{ __('cms::site.footer.phone') }}</a>
                    <a href="mailto:info@example.com">{{ __('cms::site.footer.email') }}</a>
                </address>
                <div class="c-site-footer__social">
                    <a href="https://linkedin.com" aria-label="{{ __('cms::site.footer.linkedin') }}">in</a>
                    <a href="https://instagram.com" aria-label="{{ __('cms::site.footer.instagram') }}">ig</a>
                </div>
            </div>
        </div>
        <div class="c-site-footer__meta">
            <small>© {{ date('Y') }} {{ config('app.name') }}. {{ __('cms::site.footer.rights') }}</small>
            <div class="u-cluster">
                <a href="{{ $isEn ? route('cms.en.kvkk') : route('cms.kvkk') }}">{{ __('cms::site.navigation.kvkk') }}</a>
                <a href="{{ $isEn ? route('cms.en.sitemap') : route('cms.sitemap') }}">{{ __('cms::site.footer.sitemap') }}</a>
            </div>
        </div>
    </div>
</footer>
