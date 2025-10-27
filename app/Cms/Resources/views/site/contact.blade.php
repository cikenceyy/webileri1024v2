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

    <div class="p-contact">
        <section class="p-section p-section--hero" data-module="reveal">
            <div class="u-container u-container--wide u-stack-24">
                <div class="p-hero">
                    <div class="u-stack-16">
                        <h1>{{ $hero['title'] ?? __('cms::site.contact.hero.title') }}</h1>
                        <p class="p-lead">{{ $hero['subtitle'] ?? __('cms::site.contact.hero.subtitle') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-stack-32">
                <div class="p-contact__grid">
                    <div class="c-card u-stack-16">
                        <h2>{{ __('cms::site.contact.reach_us') }}</h2>
                        <div class="u-stack-12">
                            <p>{{ $contactCard['address'] ?? $defaultAddress }}</p>
                            <a href="tel:{{ $contactCard['phone'] ?? '+902123334455' }}">{{ $contactCard['phone'] ?? __('cms::site.contact.defaults.phone') }}</a>
                            <a href="mailto:{{ $contactCard['email'] ?? $infoEmail }}">{{ $contactCard['email'] ?? $infoEmail }}</a>
                            @if(!empty($contactCard['hours']))
                                <span class="u-text-secondary">{{ $contactCard['hours'] }}</span>
                            @endif
                        </div>
                        <div class="u-stack-8">
                            <strong>{{ $defaultProjectLabel }}</strong>
                            <a href="mailto:{{ $notifyEmail }}">{{ $notifyEmail }}</a>
                        </div>
                        @if(!empty($socialLinks))
                            <div class="u-cluster">
                                @foreach($socialLinks as $link)
                                    <a href="{{ $link['url'] ?? '#' }}" target="_blank" rel="noopener">{{ $link['name'] ?? __('cms::site.contact.social.placeholder') }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="c-map-card" data-module="map-on-demand skeletons" data-map-src="{{ $map }}">
                        <h2>{{ __('cms::site.contact.visit_us') }}</h2>
                        <div class="c-map-placeholder u-ratio-16x9">
                            <button class="c-button c-button--outline" type="button" data-map-trigger>{{ __('cms::site.contact.load_map') }}</button>
                        </div>
                    </div>
                    <div class="c-card u-stack-16">
                        <h2>{{ $formCopy['title'] ?? __('cms::site.contact.share_project') }}</h2>
                        @if(!empty($formCopy['subtitle']))
                            <p class="u-text-secondary">{{ $formCopy['subtitle'] }}</p>
                        @endif
                        @if(session('status'))
                            <div class="c-card u-stack-8 u-border-subtle" role="status" aria-live="polite">{{ session('status') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="c-card u-stack-8 u-border-subtle" role="alert">{{ $errors->first() }}</div>
                        @endif
                        <form action="{{ $pageLocale === 'en' ? route('cms.en.contact.submit') : route('cms.contact.submit') }}" method="POST" class="c-form" data-module="contact-form" novalidate>
                            @csrf
                            <input type="hidden" name="submitted_at" value="{{ time() }}">
                            <div class="u-grid-two">
                                <label class="c-form__group">
                                    <span>{{ __('cms::site.contact.form.name') }}</span>
                                    <input type="text" name="name" required>
                                </label>
                                <label class="c-form__group">
                                    <span>{{ __('cms::site.contact.form.company') }}</span>
                                    <input type="text" name="company">
                                </label>
                            </div>
                            <div class="u-grid-two">
                                <label class="c-form__group">
                                    <span>{{ __('cms::site.contact.form.email') }}</span>
                                    <input type="email" name="email" required>
                                </label>
                                <label class="c-form__group">
                                    <span>{{ __('cms::site.contact.form.phone') }}</span>
                                    <input type="tel" name="phone">
                                </label>
                            </div>
                            <label class="c-form__group">
                                <span>{{ __('cms::site.contact.form.subject') }}</span>
                                <input type="text" name="subject" required>
                            </label>
                            <label class="c-form__group">
                                <span>{{ __('cms::site.contact.form.message') }}</span>
                                <textarea name="message" rows="4" required></textarea>
                            </label>
                            <div class="u-visually-hidden">
                                <label>{{ __('cms::site.contact.form.honeypot') }}
                                    <input type="text" name="website" autocomplete="off">
                                </label>
                            </div>
                            <button class="c-button c-button--primary" type="submit" data-module="beacon" data-beacon-event="form-submit" data-beacon-payload="contact">{{ __('cms::site.contact.form.send') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
