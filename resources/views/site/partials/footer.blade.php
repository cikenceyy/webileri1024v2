@php
    $isEn = ($locale ?? app()->getLocale()) === 'en';
@endphp
<footer class="site-footer">
    <div class="site-container">
        <div class="site-footer__grid grid-auto">
            <div>
                <h2 class="site-footer__brand">{{ config('app.name') }}</h2>
                <p class="site-footer__text">{{ $isEn ? 'Innovative industrial solutions with human-centered technology.' : 'İnsanı merkeze alan teknolojilerle yenilikçi endüstriyel çözümler.' }}</p>
            </div>
            <div>
                <h3 class="site-footer__title">{{ $isEn ? 'Explore' : 'Keşfet' }}</h3>
                <ul class="site-footer__links stack-xs">
                    <li><a href="{{ $isEn ? route('cms.en.corporate') : route('cms.corporate') }}">{{ $isEn ? 'Corporate' : 'Kurumsal' }}</a></li>
                    <li><a href="{{ $isEn ? route('cms.en.products') : route('cms.products') }}">{{ $isEn ? 'Products' : 'Ürünler' }}</a></li>
                    <li><a href="{{ $isEn ? route('cms.en.catalogs') : route('cms.catalogs') }}">{{ $isEn ? 'Catalogs' : 'Kataloglar' }}</a></li>
                    <li><a href="{{ $isEn ? route('cms.en.kvkk') : route('cms.kvkk') }}">KVKK</a></li>
                </ul>
            </div>
            <div>
                <h3 class="site-footer__title">{{ $isEn ? 'Contact' : 'İletişim' }}</h3>
                <address class="site-footer__text stack-xs">
                    <span>İstanbul, TR</span>
                    <a href="tel:+902123334455">+90 212 333 44 55</a>
                    <a href="mailto:info@example.com">info@example.com</a>
                </address>
                <div class="site-footer__social cluster">
                    <a href="https://linkedin.com" aria-label="LinkedIn">in</a>
                    <a href="https://instagram.com" aria-label="Instagram">ig</a>
                </div>
            </div>
        </div>
        <div class="site-footer__bottom">
            <small>© {{ date('Y') }} {{ config('app.name') }}. {{ $isEn ? 'All rights reserved.' : 'Tüm hakları saklıdır.' }}</small>
        </div>
    </div>
</footer>
