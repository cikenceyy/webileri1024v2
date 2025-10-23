<footer class="site-footer mt-5" data-analytics-section="footer">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="text-muted">&copy; {{ now()->year }} {{ config('app.name') }}</div>
            <div class="footer-links">
                <a class="me-3" href="{{ $locale === 'en' ? url('/en/products') : url('/urunler') }}">{{ $locale === 'en' ? 'Products' : 'Ürünler' }}</a>
                <a class="me-3" href="{{ $locale === 'en' ? url('/en/catalogs') : url('/kataloglar') }}">{{ $locale === 'en' ? 'Catalogs' : 'Kataloglar' }}</a>
                <a class="me-3" href="{{ $locale === 'en' ? url('/en/info/kvkk') : url('/bilgi/kvkk') }}">KVKK</a>
                <a href="{{ $locale === 'en' ? url('/en/contact') : url('/iletisim') }}">{{ $locale === 'en' ? 'Contact' : 'İletişim' }}</a>
            </div>
        </div>
    </div>
</footer>
