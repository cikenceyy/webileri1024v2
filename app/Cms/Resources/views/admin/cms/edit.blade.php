@extends('layouts.admin')

@php($locales = ['tr' => 'Türkçe', 'en' => 'English'])

@section('content')
    <div class="container-fluid py-4" data-tab-container>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <a href="{{ route('cms.admin.pages.index') }}" class="btn btn-link px-0">&larr; {{ __('Back to pages') }}</a>
                <h1 class="h3 mb-0">{{ $pageConfig['label'] ?? ucfirst($pageKey) }}</h1>
                @if(!empty($pageConfig['description']))
                    <p class="text-muted mb-0 small">{{ $pageConfig['description'] }}</p>
                @endif
            </div>
            <div class="d-flex gap-2" data-tab-buttons>
                <button class="btn btn-outline-secondary active" type="button" data-tab-target="content">{{ __('Content') }}</button>
                <button class="btn btn-outline-secondary" type="button" data-tab-target="seo">SEO</button>
                <button class="btn btn-outline-secondary" type="button" data-tab-target="scripts">{{ __('Scripts') }}</button>
                <button class="btn btn-outline-secondary" type="button" data-tab-target="emails">{{ __('Emails') }}</button>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" enctype="multipart/form-data" action="{{ route('cms.admin.pages.update', $pageKey) }}" id="cms-form">
            @csrf

            <p class="text-muted small mb-4">{{ __('EN alanını boş bırakırsanız Türkçe içerik otomatik olarak gösterilecektir.') }}</p>

            <div class="tab-panel" data-tab-panel="content">
                <div class="accordion" id="cms-blocks">
                    @foreach($pageConfig['blocks'] ?? [] as $blockKey => $definition)
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header" id="heading-{{ $blockKey }}">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $blockKey }}" aria-expanded="true">
                                    {{ $definition['label'] ?? ucfirst(str_replace('_', ' ', $blockKey)) }}
                                </button>
                            </h2>
                            <div id="collapse-{{ $blockKey }}" class="accordion-collapse collapse show" data-bs-parent="#cms-blocks">
                                <div class="accordion-body">
                                    @if(!empty($definition['help']))
                                        <p class="text-muted small mb-3">{{ $definition['help'] }}</p>
                                    @endif

                                    <div class="row g-4">
                                        @foreach($locales as $localeKey => $localeLabel)
                                            @php($blockValues = data_get($content, "$localeKey.blocks.$blockKey", []))
                                            <div class="col-lg-6" data-block-locale="{{ $localeKey }}">
                                                <h3 class="h6 text-uppercase text-muted mb-3">{{ $localeLabel }}</h3>
                                                @include('cms::admin.cms.partials.block-fields', [
                                                    'mode' => 'form',
                                                    'definition' => $definition,
                                                    'blockKey' => $blockKey,
                                                    'localeKey' => $localeKey,
                                                    'values' => $blockValues,
                                                ])
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="tab-panel d-none" data-tab-panel="seo">
                @include('cms::admin.cms.seo', ['locales' => $locales, 'seo' => $seo])
            </div>

            <div class="tab-panel d-none" data-tab-panel="scripts">
                @include('cms::admin.cms.scripts', ['locales' => $locales, 'scripts' => $scripts])
            </div>

            <div class="tab-panel d-none" data-tab-panel="emails">
                <div class="card">
                    <div class="card-header">
                        <h2 class="h5 mb-0">{{ __('Notification Emails') }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Info email') }}</label>
                                <input type="email" class="form-control" name="emails[info_email]" value="{{ $emails['info_email'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Notify email') }}</label>
                                <input type="email" class="form-control" name="emails[notify_email]" value="{{ $emails['notify_email'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-tab-container]').forEach((container) => {
            const buttons = Array.from(container.querySelectorAll('[data-tab-target]'));
            const panels = Array.from(container.querySelectorAll('[data-tab-panel]'));

            buttons.forEach((button) => {
                button.addEventListener('click', () => {
                    const target = button.getAttribute('data-tab-target');
                    buttons.forEach((btn) => btn.classList.toggle('active', btn === button));
                    panels.forEach((panel) => {
                        panel.classList.toggle('d-none', panel.getAttribute('data-tab-panel') !== target);
                    });
                });
            });
        });

        document.addEventListener('click', (event) => {
            if (event.target.matches('[data-repeater-add]')) {
                const wrapper = event.target.closest('[data-repeater]');
                if (!wrapper) {
                    return;
                }

                const container = wrapper.querySelector('[data-repeater-items]');
                const template = wrapper.querySelector('[data-repeater-template]');
                if (!container || !template) {
                    return;
                }

                const index = container.children.length;
                const clone = template.content.cloneNode(true);
                clone.querySelectorAll('[name]').forEach((input) => {
                    const original = input.getAttribute('name');
                    if (original) {
                        input.setAttribute('name', original.replace('__INDEX__', index));
                    }
                });
                container.appendChild(clone);

                const emptyState = wrapper.querySelector('[data-repeater-empty]');
                if (emptyState) {
                    emptyState.classList.add('d-none');
                }
            }

            if (event.target.matches('[data-repeater-remove]')) {
                const item = event.target.closest('[data-repeater-item]');
                if (!item) {
                    return;
                }
                const wrapper = event.target.closest('[data-repeater]');
                item.remove();
                if (wrapper) {
                    const container = wrapper.querySelector('[data-repeater-items]');
                    if (container && container.children.length === 0) {
                        const emptyState = wrapper.querySelector('[data-repeater-empty]');
                        if (emptyState) {
                            emptyState.classList.remove('d-none');
                        }
                    }
                }
            }
        });
    </script>
@endpush
