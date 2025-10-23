@extends('cms::site.layout', ['pageId' => 'contact', 'locale' => $locale ?? app()->getLocale()])

@push('site-styles')
    @vite('app/Cms/Resources/assets/scss/site/contact.scss')
@endpush

@push('site-scripts')
    @vite('app/Cms/Resources/assets/js/site/contact.js')
@endpush

@section('content')
    @php
        $pageLocale = $locale ?? app()->getLocale();
        $hero = data_get($data, 'blocks.page_hero', data_get($data, 'blocks.hero', []));
        $contactCard = data_get($data, 'blocks.contact_cards', []);
        $socialLinks = data_get($data, 'blocks.social_links', []);
        $map = data_get($data, 'blocks.map.map_embed');
        $formCopy = data_get($data, 'blocks.form_copy', []);
        $infoEmail = $emails['info_email'] ?? 'info@example.com';
        $notifyEmail = $emails['notify_email'] ?? 'sales@example.com';
        $defaultAddress = \Illuminate\Support\Facades\Lang::get('cms::site.contact.defaults.address', [], $pageLocale);
        $defaultProjectLabel = \Illuminate\Support\Facades\Lang::get('cms::site.contact.project_inquiries', [], $pageLocale);
    @endphp
    <section class="pattern-hero contact-hero" data-module="reveal">
        <div class="stack-lg">
            <h1>{{ $hero['title'] ?? __('cms::site.contact.hero.title') }}</h1>
            <p class="lead">{{ $hero['subtitle'] ?? __('cms::site.contact.hero.subtitle') }}</p>
        </div>
    </section>

    <section class="pattern-contact" data-module="reveal">
        <div class="contact-grid">
            <div class="contact-card stack-md">
                <h2>{{ __('cms::site.contact.reach_us') }}</h2>
                <div class="stack-xs">
                    <p>{{ $contactCard['address'] ?? $defaultAddress }}</p>
                    <a href="tel:{{ $contactCard['phone'] ?? '+902123334455' }}">{{ $contactCard['phone'] ?? __('cms::site.contact.defaults.phone') }}</a>
                    <a href="mailto:{{ $contactCard['email'] ?? $infoEmail }}">{{ $contactCard['email'] ?? $infoEmail }}</a>
                    @if(!empty($contactCard['hours']))
                        <span class="contact-hours">{{ $contactCard['hours'] }}</span>
                    @endif
                </div>
                <div class="stack-xs">
                    <strong>{{ $defaultProjectLabel }}</strong>
                    <a href="mailto:{{ $notifyEmail }}">{{ $notifyEmail }}</a>
                </div>
                @if(!empty($socialLinks))
                    <div class="contact-social cluster">
                        @foreach($socialLinks as $link)
                            <a href="{{ $link['url'] ?? '#' }}" target="_blank" rel="noopener">{{ $link['name'] ?? __('cms::site.contact.social.placeholder') }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="contact-card stack-md" data-module="map-on-demand skeletons" data-map-src="{{ $map }}">
                <h2>{{ __('cms::site.contact.visit_us') }}</h2>
                <div class="map-placeholder ratio-16x9">
                    <button class="btn btn-outline" type="button" data-map-trigger>{{ __('cms::site.contact.load_map') }}</button>
                </div>
            </div>
            <div class="contact-card stack-md">
                <h2>{{ $formCopy['title'] ?? __('cms::site.contact.share_project') }}</h2>
                @if(!empty($formCopy['subtitle']))
                    <p>{{ $formCopy['subtitle'] }}</p>
                @endif
                @if(session('status'))
                    <div class="alert success" role="status" aria-live="polite">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert danger" role="alert">{{ $errors->first() }}</div>
                @endif
                <form action="{{ $pageLocale === 'en' ? route('cms.en.contact.submit') : route('cms.contact.submit') }}" method="POST" class="stack-sm" data-module="contact-form" novalidate>
                    @csrf
                    <input type="hidden" name="submitted_at" value="{{ time() }}">
                    <div class="grid-two">
                        <label class="stack-xs">
                            <span>{{ __('cms::site.contact.form.name') }}</span>
                            <input type="text" name="name" required>
                        </label>
                        <label class="stack-xs">
                            <span>{{ __('cms::site.contact.form.company') }}</span>
                            <input type="text" name="company">
                        </label>
                    </div>
                    <div class="grid-two">
                        <label class="stack-xs">
                            <span>{{ __('cms::site.contact.form.email') }}</span>
                            <input type="email" name="email" required>
                        </label>
                        <label class="stack-xs">
                            <span>{{ __('cms::site.contact.form.phone') }}</span>
                            <input type="tel" name="phone">
                        </label>
                    </div>
                    <label class="stack-xs">
                        <span>{{ __('cms::site.contact.form.subject') }}</span>
                        <input type="text" name="subject" required>
                    </label>
                    <label class="stack-xs">
                        <span>{{ __('cms::site.contact.form.message') }}</span>
                        <textarea name="message" rows="4" required></textarea>
                    </label>
                    <div class="visually-hidden">
                        <label>{{ __('cms::site.contact.form.honeypot') }}
                            <input type="text" name="website" autocomplete="off">
                        </label>
                    </div>
                    <button class="btn btn-primary" type="submit" data-module="beacon" data-beacon-event="form_submit" data-beacon-payload="contact">{{ __('cms::site.contact.form.send') }}</button>
                </form>
            </div>
        </div>
    </section>
@endsection
