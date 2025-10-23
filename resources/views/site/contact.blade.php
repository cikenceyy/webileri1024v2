@extends('site.layout', ['pageId' => 'contact', 'locale' => $locale ?? app()->getLocale()])

@push('site-styles')
    @vite('resources/scss/site/contact.scss')
@endpush

@push('site-scripts')
    @vite('resources/js/site/contact.js')
@endpush

@section('content')
    @php
        $hero = data_get($data, 'blocks.hero', []);
        $coords = data_get($data, 'blocks.coords', []);
        $infoEmail = $emails['info_email'] ?? 'info@example.com';
        $notifyEmail = $emails['notify_email'] ?? 'sales@example.com';
    @endphp
    <section class="pattern-hero contact-hero" data-module="reveal">
        <div class="stack-lg">
            <h1>{{ $hero['title'] ?? ($locale === 'en' ? 'Let’s talk' : 'Hadi konuşalım') }}</h1>
            <p class="lead">{{ $hero['subtitle'] ?? ($locale === 'en' ? 'Share your challenge and we will design a tailored response.' : 'Zorluklarınızı paylaşın, size özel çözümü tasarlayalım.') }}</p>
        </div>
    </section>

    <section class="pattern-contact" data-module="reveal">
        <div class="contact-grid">
            <div class="contact-card stack-md">
                <h2>{{ $locale === 'en' ? 'Reach us' : 'Bize ulaşın' }}</h2>
                <div class="stack-xs">
                    <p>{{ $coords['address'] ?? ($locale === 'en' ? "Istanbul Technopark, TR" : "İstanbul Teknokent, TR") }}</p>
                    <a href="tel:{{ $coords['phone'] ?? '+902123334455' }}">{{ $coords['phone'] ?? '+90 212 333 44 55' }}</a>
                    <a href="mailto:{{ $coords['email'] ?? $infoEmail }}">{{ $coords['email'] ?? $infoEmail }}</a>
                </div>
                <div class="stack-xs">
                    <strong>{{ $locale === 'en' ? 'Project inquiries' : 'Proje talepleri' }}</strong>
                    <a href="mailto:{{ $notifyEmail }}">{{ $notifyEmail }}</a>
                </div>
            </div>
            <div class="contact-card stack-md" data-module="map-on-demand" data-map-src="{{ $coords['map_embed'] ?? '' }}">
                <h2>{{ $locale === 'en' ? 'Visit our HQ' : 'Merkez ofisimiz' }}</h2>
                <div class="map-placeholder ratio-16x9">
                    <button class="btn btn-outline" type="button" data-map-trigger>{{ $locale === 'en' ? 'Load map' : 'Haritayı yükle' }}</button>
                </div>
            </div>
            <div class="contact-card stack-md">
                <h2>{{ $locale === 'en' ? 'Share your project' : 'Projenizi paylaşın' }}</h2>
                @if(session('status'))
                    <div class="alert success">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert danger">{{ $errors->first() }}</div>
                @endif
                <form action="{{ $locale === 'en' ? route('cms.en.contact.submit') : route('cms.contact.submit') }}" method="POST" class="stack-sm" data-module="contact-form">
                    @csrf
                    <input type="hidden" name="submitted_at" value="{{ time() }}">
                    <div class="grid-two">
                        <label class="stack-xs">
                            <span>{{ $locale === 'en' ? 'Full name' : 'Ad Soyad' }}</span>
                            <input type="text" name="name" required>
                        </label>
                        <label class="stack-xs">
                            <span>{{ $locale === 'en' ? 'Company' : 'Şirket' }}</span>
                            <input type="text" name="company">
                        </label>
                    </div>
                    <div class="grid-two">
                        <label class="stack-xs">
                            <span>{{ $locale === 'en' ? 'Email' : 'E-posta' }}</span>
                            <input type="email" name="email" required>
                        </label>
                        <label class="stack-xs">
                            <span>{{ $locale === 'en' ? 'Phone' : 'Telefon' }}</span>
                            <input type="tel" name="phone">
                        </label>
                    </div>
                    <label class="stack-xs">
                        <span>{{ $locale === 'en' ? 'Subject' : 'Konu' }}</span>
                        <input type="text" name="subject" required>
                    </label>
                    <label class="stack-xs">
                        <span>{{ $locale === 'en' ? 'How can we help?' : 'Size nasıl yardımcı olabiliriz?' }}</span>
                        <textarea name="message" rows="4" required></textarea>
                    </label>
                    <div class="visually-hidden">
                        <label>{{ $locale === 'en' ? 'Leave empty' : 'Boş bırakın' }}
                            <input type="text" name="website" autocomplete="off">
                        </label>
                    </div>
                    <button class="btn btn-primary" type="submit">{{ $locale === 'en' ? 'Send message' : 'Mesaj gönder' }}</button>
                </form>
            </div>
        </div>
    </section>
@endsection
