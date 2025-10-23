@extends('site.layout', ['pageId' => 'home', 'locale' => $locale ?? app()->getLocale()])

@push('critical-css')
    <style>
        .home-hero{display:grid;gap:var(--space-24);align-items:center;padding-block:clamp(4rem,6vw,6rem)}
        @media (min-width: 48rem){.home-hero{grid-template-columns:repeat(2,minmax(0,1fr));}}
        .home-hero__media{position:relative;border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);}
        .home-hero__media img{width:100%;height:100%;object-fit:cover;display:block;}
    </style>
@endpush

@push('site-styles')
    @vite('resources/scss/site/home.scss')
@endpush

@push('site-scripts')
    @vite('resources/js/site/home.js')
@endpush

@section('content')
    @php
        $hero = data_get($data, 'blocks.hero', []);
        $uspItems = data_get($data, 'blocks.usp_grid', []);
        $ctaBand = data_get($data, 'blocks.cta_band', []);
        $placeholder = fn ($label, $width, $height) => 'data:image/svg+xml;utf8,' . rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$width} {$height}'><rect width='100%' height='100%' fill='%23e4e7ec'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='Inter, sans-serif' font-size='24' fill='%23667085'>{$label}</text></svg>");
        $heroImage = $hero['image'] ?? $placeholder('Hero', 1280, 720);
    @endphp
    <section class="pattern-hero home-hero" data-module="reveal">
        <div class="stack-lg">
            <p class="eyebrow">{{ $locale === 'en' ? 'Trusted by industry leaders' : 'Sektör liderlerinin tercihi' }}</p>
            <h1 class="display">{{ $hero['title'] ?? ($locale === 'en' ? 'Engineering the future of automation' : 'Otomasyonun geleceğini tasarlıyoruz') }}</h1>
            <p class="lead">{{ $hero['subtitle'] ?? ($locale === 'en' ? 'We deliver resilient, human-centered manufacturing ecosystems.' : 'Dayanıklı ve insan odaklı üretim ekosistemleri kuruyoruz.') }}</p>
            @if(!empty($hero['cta_link']) && !empty($hero['cta_text']))
                <div class="cluster">
                    <a class="btn btn-primary" href="{{ $hero['cta_link'] }}">{{ $hero['cta_text'] }}</a>
                </div>
            @else
                <div class="cluster">
                    <a class="btn btn-primary" href="{{ $locale === 'en' ? route('cms.en.contact') : route('cms.contact') }}">{{ $locale === 'en' ? 'Request a quote' : 'Teklif isteyin' }}</a>
                </div>
            @endif
        </div>
        <div class="home-hero__media" data-module="lazy-media">
            <img src="{{ $heroImage }}" srcset="{{ $heroImage }} 1280w, {{ $heroImage }} 640w" sizes="(min-width: 62rem) 50vw, 100vw" width="1280" height="720" alt="{{ $hero['title'] ?? 'Automation hero image' }}" loading="eager">
        </div>
    </section>

    <section class="pattern-usp" data-module="reveal">
        <div class="section-head stack-sm">
            <h2>{{ $locale === 'en' ? 'Why choose us?' : 'Neden biz?' }}</h2>
            <p>{{ $locale === 'en' ? 'Reliable delivery, scalable production, dedicated engineering.' : 'Güvenilir teslimat, ölçeklenebilir üretim, adanmış mühendislik.' }}</p>
        </div>
        <div class="usp-grid grid-auto">
            @forelse($uspItems as $item)
                <article class="usp-card stack-sm">
                    @if(!empty($item['icon']))
                        <img class="usp-card__icon" src="{{ $item['icon'] }}" width="48" height="48" alt="" loading="lazy">
                    @endif
                    <h3>{{ $item['title'] ?? ($locale === 'en' ? 'High precision' : 'Yüksek hassasiyet') }}</h3>
                    <p>{{ $item['description'] ?? ($locale === 'en' ? 'Precision-focused production processes built for longevity.' : 'Uzun ömür için tasarlanmış hassas odaklı üretim süreçleri.') }}</p>
                </article>
            @empty
                @for($i=0;$i<3;$i++)
                    <article class="usp-card stack-sm">
                        <div class="usp-card__icon placeholder-icon" aria-hidden="true"></div>
                        <h3>{{ $locale === 'en' ? 'Expert team' : 'Uzman ekip' }}</h3>
                        <p>{{ $locale === 'en' ? 'Cross-functional experts delivering measurable impact.' : 'Ölçülebilir etki sağlayan disiplinler arası uzmanlar.' }}</p>
                    </article>
                @endfor
            @endforelse
        </div>
    </section>

    <section class="pattern-product-grid" data-module="reveal">
        <div class="section-head stack-sm">
            <h2>{{ $locale === 'en' ? 'Featured products' : 'Öne çıkan ürünler' }}</h2>
            <p>{{ $locale === 'en' ? 'Discover modular systems ready for integration.' : 'Entegrasyona hazır modüler sistemleri keşfedin.' }}</p>
        </div>
        <div class="product-grid grid-auto">
            @forelse($featuredProducts as $product)
                <article class="product-card stack-sm">
                    <div class="product-card__media ratio-3x2">
                        @php $productCover = $product['cover_image'] ?? $placeholder('Product', 640, 480); @endphp
                        <img src="{{ $productCover }}" srcset="{{ $productCover }} 640w" sizes="(min-width: 62rem) 240px, 50vw" width="640" height="480" alt="{{ $product['name'] ?? 'Product' }}" loading="lazy">
                    </div>
                    <div class="stack-xs">
                        <h3>{{ $product['name'] ?? ($locale === 'en' ? 'Modular Conveyor' : 'Modüler Konveyör') }}</h3>
                        <p>{{ $product['short_desc'] ?? ($locale === 'en' ? 'Configurable conveyor platform for agile plants.' : 'Çevik tesisler için yapılandırılabilir konveyör platformu.') }}</p>
                        @php
                            $productUrl = !empty($product['slug'])
                                ? ($locale === 'en' ? route('cms.en.product.show', $product['slug']) : route('cms.product.show', $product['slug']))
                                : ($locale === 'en' ? route('cms.en.products') : route('cms.products'));
                        @endphp
                        <a class="btn btn-outline" href="{{ $productUrl }}">{{ $locale === 'en' ? 'View details' : 'Detayları gör' }}</a>
                    </div>
                </article>
            @empty
                @for($i=0;$i<6;$i++)
                    <article class="product-card stack-sm placeholder">
                        <div class="product-card__media ratio-3x2 placeholder-block"></div>
                        <div class="stack-xs">
                            <h3>{{ $locale === 'en' ? 'Product headline' : 'Ürün başlığı' }}</h3>
                            <p>{{ $locale === 'en' ? 'Flexible solution description placeholder.' : 'Esnek çözüm açıklaması yer tutucusu.' }}</p>
                            <span class="btn btn-outline is-disabled">{{ $locale === 'en' ? 'Coming soon' : 'Çok yakında' }}</span>
                        </div>
                    </article>
                @endfor
            @endforelse
        </div>
    </section>

    <section class="pattern-catalog-grid" data-module="reveal">
        <div class="section-head stack-sm">
            <h2>{{ $locale === 'en' ? 'Download catalogs' : 'Katalogları indirin' }}</h2>
            <p>{{ $locale === 'en' ? 'Comprehensive product brochures ready for your team.' : 'Ekibiniz için hazır kapsamlı ürün broşürleri.' }}</p>
        </div>
        <div class="catalog-grid grid-auto">
            @forelse($catalogs as $catalog)
                <article class="catalog-card stack-sm">
                    <div class="catalog-card__media ratio-4x3">
                        @php $catalogCover = $catalog['cover'] ?? $placeholder('Catalog', 640, 480); @endphp
                        <img src="{{ $catalogCover }}" srcset="{{ $catalogCover }} 640w" sizes="(min-width: 62rem) 240px, 50vw" width="640" height="480" alt="{{ $catalog['title'] ?? 'Catalog' }}" loading="lazy">
                    </div>
                    <div class="stack-xs">
                        <h3>{{ $catalog['title'] ?? ($locale === 'en' ? 'Annual solutions' : 'Yıllık çözümler') }}</h3>
                        <a class="btn btn-outline" href="{{ $catalog['file'] ?? '#' }}" target="_blank" rel="noopener">{{ $locale === 'en' ? 'Open PDF' : 'PDF Aç' }}</a>
                    </div>
                </article>
            @empty
                @for($i=0;$i<6;$i++)
                    <article class="catalog-card stack-sm placeholder">
                        <div class="catalog-card__media ratio-4x3 placeholder-block"></div>
                        <div class="stack-xs">
                            <h3>{{ $locale === 'en' ? 'Catalog headline' : 'Katalog başlığı' }}</h3>
                            <span class="btn btn-outline is-disabled">{{ $locale === 'en' ? 'Coming soon' : 'Çok yakında' }}</span>
                        </div>
                    </article>
                @endfor
            @endforelse
        </div>
    </section>

    <section class="pattern-feature" data-module="reveal">
        <div class="feature-band stack-sm">
            <h2>{{ $ctaBand['title'] ?? ($locale === 'en' ? 'Scale with confidence' : 'Güvenle ölçekleyin') }}</h2>
            <p>{{ $locale === 'en' ? 'Partner with our engineering team to accelerate your next milestone.' : 'Mühendislik ekibimizle ortak olun, bir sonraki dönüm noktanızı hızlandırın.' }}</p>
            <a class="btn btn-primary" href="{{ $ctaBand['cta_link'] ?? ($locale === 'en' ? route('cms.en.contact') : route('cms.contact')) }}">{{ $ctaBand['cta_text'] ?? ($locale === 'en' ? 'Talk to an expert' : 'Uzmanla görüşün') }}</a>
        </div>
    </section>
@endsection
