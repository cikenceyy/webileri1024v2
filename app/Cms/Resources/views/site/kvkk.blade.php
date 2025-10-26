@extends('cms::site.layout', ['pageId' => 'kvkk', 'locale' => $locale ?? app()->getLocale()])

@push('site-styles')
    @vite('app/Cms/Resources/assets/scss/site/kvkk.scss')
@endpush

@push('site-scripts')
    @vite('app/Cms/Resources/assets/js/site/kvkk.js')
@endpush

@section('content')
    @php
        $pageLocale = $locale ?? app()->getLocale();
        $hero = data_get($data, 'blocks.page_hero', data_get($data, 'blocks.hero', []));
        $summary = data_get($data, 'blocks.summary', []);
        $purposes = data_get($data, 'blocks.purposes', []);
        $retention = data_get($data, 'blocks.retention', []);
        $rights = data_get($data, 'blocks.rights', []);
        $contactPrivacy = data_get($data, 'blocks.contact_privacy', []);
        $attachment = data_get($data, 'blocks.pdf.file');
    @endphp

    <div class="p-kvkk">
        <section class="p-section p-section--hero" data-module="reveal">
            <div class="u-container u-container--narrow u-stack-16">
                <div class="p-hero">
                    <div class="u-stack-12">
                        <h1>{{ $hero['title'] ?? __('cms::site.kvkk.page_hero.title') }}</h1>
                        <p class="p-lead">{{ $hero['subtitle'] ?? __('cms::site.kvkk.page_hero.subtitle') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="p-section" data-module="reveal">
            <div class="u-container u-container--wide u-stack-32">
                <div class="p-layout" data-module="toc-observer" data-toc-target="#kvkk-content">
                    <aside class="p-toc">
                        <h2>{{ __('cms::site.kvkk.toc.title') }}</h2>
                        <nav>
                            <ol class="p-toc__list">
                                <li><a class="p-toc__link" href="#summary">{{ __('cms::site.kvkk.sections.summary') }}</a></li>
                                <li><a class="p-toc__link" href="#purposes">{{ __('cms::site.kvkk.sections.purposes') }}</a></li>
                                <li><a class="p-toc__link" href="#retention">{{ __('cms::site.kvkk.sections.retention') }}</a></li>
                                <li><a class="p-toc__link" href="#rights">{{ __('cms::site.kvkk.sections.rights') }}</a></li>
                                <li><a class="p-toc__link" href="#contact">{{ __('cms::site.kvkk.sections.contact') }}</a></li>
                            </ol>
                        </nav>
                    </aside>
                    <article id="kvkk-content" class="p-content">
                        <section id="summary" class="u-stack-16">
                            <h2>{{ __('cms::site.kvkk.summary.heading') }}</h2>
                            <p>{{ $summary['intro'] ?? __('cms::site.kvkk.summary.text') }}</p>
                        </section>

                        <section id="purposes" class="u-stack-16">
                            <h2>{{ __('cms::site.kvkk.purposes.heading') }}</h2>
                            <ol class="kvkk-list">
                                @forelse($purposes as $purpose)
                                    <li>
                                        <h3>{{ $purpose['title'] ?? __('cms::site.kvkk.purposes.default_title') }}</h3>
                                        <p>{{ $purpose['description'] ?? __('cms::site.kvkk.purposes.default_description') }}</p>
                                    </li>
                                @empty
                                    @foreach(\Illuminate\Support\Facades\Lang::get('cms::site.kvkk.purposes.defaults', [], $pageLocale) as $fallback)
                                        <li data-skeleton>
                                            <h3>{{ $fallback['title'] ?? __('cms::site.kvkk.purposes.default_title') }}</h3>
                                            <p>{{ $fallback['description'] ?? __('cms::site.kvkk.purposes.default_description') }}</p>
                                        </li>
                                    @endforeach
                                @endforelse
                            </ol>
                        </section>

                        <section id="retention" class="u-stack-16">
                            <h2>{{ __('cms::site.kvkk.retention.heading') }}</h2>
                            <div class="kvkk-table" role="table">
                                <div class="kvkk-table__row kvkk-table__row--head" role="row">
                                    <div role="columnheader">{{ __('cms::site.kvkk.retention.columns.type') }}</div>
                                    <div role="columnheader">{{ __('cms::site.kvkk.retention.columns.period') }}</div>
                                    <div role="columnheader">{{ __('cms::site.kvkk.retention.columns.details') }}</div>
                                </div>
                                @forelse($retention as $item)
                                    <div class="kvkk-table__row" role="row">
                                        <div role="cell">{{ $item['data_type'] ?? __('cms::site.kvkk.retention.default_type') }}</div>
                                        <div role="cell">{{ $item['period'] ?? __('cms::site.kvkk.retention.default_period') }}</div>
                                        <div role="cell">{{ $item['description'] ?? __('cms::site.kvkk.retention.default_description') }}</div>
                                    </div>
                                @empty
                                    @foreach(\Illuminate\Support\Facades\Lang::get('cms::site.kvkk.retention.defaults', [], $pageLocale) as $fallback)
                                        <div class="kvkk-table__row" role="row" data-skeleton>
                                            <div role="cell">{{ $fallback['data_type'] ?? __('cms::site.kvkk.retention.default_type') }}</div>
                                            <div role="cell">{{ $fallback['period'] ?? __('cms::site.kvkk.retention.default_period') }}</div>
                                            <div role="cell">{{ $fallback['description'] ?? __('cms::site.kvkk.retention.default_description') }}</div>
                                        </div>
                                    @endforeach
                                @endforelse
                            </div>
                        </section>

                        <section id="rights" class="u-stack-16">
                            <h2>{{ __('cms::site.kvkk.rights.heading') }}</h2>
                            <ul class="kvkk-list">
                                @forelse($rights as $right)
                                    <li>
                                        <h3>{{ $right['title'] ?? __('cms::site.kvkk.rights.default_title') }}</h3>
                                        <p>{{ $right['description'] ?? __('cms::site.kvkk.rights.default_description') }}</p>
                                    </li>
                                @empty
                                    @foreach(\Illuminate\Support\Facades\Lang::get('cms::site.kvkk.rights.defaults', [], $pageLocale) as $fallback)
                                        <li data-skeleton>
                                            <h3>{{ $fallback['title'] ?? __('cms::site.kvkk.rights.default_title') }}</h3>
                                            <p>{{ $fallback['description'] ?? __('cms::site.kvkk.rights.default_description') }}</p>
                                        </li>
                                    @endforeach
                                @endforelse
                            </ul>
                        </section>

                        <section id="contact" class="u-stack-16">
                            <h2>{{ __('cms::site.kvkk.contact.heading') }}</h2>
                            <p><strong>{{ __('cms::site.kvkk.contact.officer') }}</strong> {{ $contactPrivacy['officer'] ?? __('cms::site.kvkk.contact.default_officer') }}</p>
                            <p><a href="mailto:{{ $contactPrivacy['email'] ?? 'kvkk@example.com' }}">{{ $contactPrivacy['email'] ?? 'kvkk@example.com' }}</a></p>
                            <p>{{ $contactPrivacy['address'] ?? __('cms::site.kvkk.contact.address') }}</p>

                            @if($attachment)
                                <a class="p-pdf" data-module="beacon" data-beacon-event="kvkk-pdf-open" href="{{ $attachment }}" target="_blank" rel="noopener">{{ __('cms::site.kvkk.download') }}</a>
                            @else
                                <span class="p-pdf is-disabled">{{ __('cms::site.kvkk.download_placeholder') }}</span>
                            @endif
                        </section>
                    </article>
                </div>
            </div>
        </section>
    </div>
@endsection
