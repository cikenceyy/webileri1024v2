<header class="site-header" data-analytics-section="header">
    <div class="container d-flex align-items-center justify-content-between py-3">
        <a href="{{ $locale === 'en' ? url('/en') : url('/') }}" class="site-logo">{{ config('app.name') }}</a>
        <nav class="site-nav">
            <ul class="nav">
                <li class="nav-item"><a class="nav-link" href="{{ $locale === 'en' ? url('/en') : url('/') }}">{{ $locale === 'en' ? 'Home' : 'Anasayfa' }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ $locale === 'en' ? url('/en/corporate') : url('/kurumsal') }}">{{ $locale === 'en' ? 'Corporate' : 'Kurumsal' }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ $locale === 'en' ? url('/en/products') : url('/urunler') }}">{{ $locale === 'en' ? 'Products' : 'Ürünler' }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ $locale === 'en' ? url('/en/catalogs') : url('/kataloglar') }}">{{ $locale === 'en' ? 'Catalogs' : 'Kataloglar' }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ $locale === 'en' ? url('/en/info/kvkk') : url('/bilgi/kvkk') }}">{{ $locale === 'en' ? 'KVKK' : 'KVKK' }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ $locale === 'en' ? url('/en/contact') : url('/iletisim') }}">{{ $locale === 'en' ? 'Contact' : 'İletişim' }}</a></li>
            </ul>
        </nav>
    </div>
</header>
