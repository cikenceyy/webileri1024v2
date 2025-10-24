@extends('layouts.admin')

@php($locales = ['tr' => 'Türkçe', 'en' => 'English'])

@push('styles')
    @vite('app/Cms/Resources/assets/scss/admin/editor.scss')
@endpush

@push('scripts')
    <script type="application/json" id="cms-editor-state">
        {!! json_encode([
            'page' => $pageKey,
            'previewToken' => $previewToken,
            'previewUrl' => $previewUrl,
            'locales' => $locales,
            'activeLocale' => $activeLocale,
            'routes' => [
                'save' => route('cms.admin.save'),
                'preview_apply' => route('cms.admin.preview.apply'),
                'preview_discard' => route('cms.admin.preview.discard'),
                'preview_upload' => route('cms.admin.preview.upload'),
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    @vite('app/Cms/Resources/assets/js/admin/editor/index.js')
@endpush

@section('content')
    <div class="cms-editor" data-cms-editor>
        <div class="cms-editor__header">
            <div>
                <a href="{{ route('cms.admin.pages.index') }}" class="btn btn-link px-0">&larr; {{ __('Back to pages') }}</a>
                <h1 class="h3 mb-1">{{ $pageConfig['label'] ?? ucfirst($pageKey) }}</h1>
                @if(!empty($pageConfig['description']))
                    <p class="text-muted small mb-0">{{ $pageConfig['description'] }}</p>
                @endif
            </div>
            <div class="cms-editor__actions">
                <span class="badge bg-warning text-dark d-none" data-editor-dirty>{{ __('Unsaved changes') }}</span>
                <button class="btn btn-outline-secondary" type="button" data-editor-discard>{{ __('Discard preview') }}</button>
                <button class="btn btn-primary" type="button" data-editor-save>{{ __('Save & publish') }}</button>
            </div>
        </div>
        <div class="cms-editor__body">
            <div class="cms-editor__canvas">
                <iframe src="{{ $previewUrl }}" title="CMS preview" data-editor-canvas></iframe>
            </div>
            <div class="cms-editor__inspector">
                <div class="cms-editor__tabs" role="tablist">
                    <button class="editor-tab is-active" type="button" data-editor-tab="content">{{ __('Content') }}</button>
                    <button class="editor-tab" type="button" data-editor-tab="seo">SEO</button>
                    <button class="editor-tab" type="button" data-editor-tab="scripts">{{ __('Scripts') }}</button>
                    <button class="editor-tab" type="button" data-editor-tab="emails">{{ __('Emails') }}</button>
                </div>
                <form id="cms-editor-form" data-editor-form enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="page" value="{{ $pageKey }}">
                    <input type="hidden" name="preview_token" value="{{ $previewToken }}">

                    <div class="editor-panel" data-editor-panel="content">
                        <div class="locale-tabs" role="tablist">
                            @foreach($locales as $localeKey => $label)
                                <button type="button" class="locale-tab @if($localeKey === $activeLocale) is-active @endif" data-locale-tab="{{ $localeKey }}">{{ $label }}</button>
                            @endforeach
                        </div>
                        <div class="locale-panels">
                            @foreach($locales as $localeKey => $label)
                                @php($blockValues = data_get($content, "$localeKey.blocks", []))
                                <div class="locale-panel @if($localeKey === $activeLocale) is-active @endif" data-locale-panel="{{ $localeKey }}">
                                    <p class="text-muted small mb-3">{{ __('EN boş bırakılırsa TR içeriği gösterilecektir.') }}</p>
                                    <div class="accordion" id="editor-blocks-{{ $localeKey }}">
                                        @foreach($pageConfig['blocks'] ?? [] as $blockKey => $definition)
                                            @php($isRepeater = !empty($definition['repeater']))
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-{{ $localeKey }}-{{ $blockKey }}">
                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $localeKey }}-{{ $blockKey }}" aria-expanded="true">
                                                        {{ $definition['label'] ?? ucfirst(str_replace('_', ' ', $blockKey)) }}
                                                    </button>
                                                </h2>
                                                <div id="collapse-{{ $localeKey }}-{{ $blockKey }}" class="accordion-collapse collapse show">
                                                    <div class="accordion-body" data-block="{{ $blockKey }}" data-locale="{{ $localeKey }}">
                                                        @if(!empty($definition['help']))
                                                            <p class="text-muted small mb-3">{{ $definition['help'] }}</p>
                                                        @endif

                                                        @if($isRepeater)
                                                            @php($items = $blockValues[$blockKey] ?? [])
                                                            <div class="repeater" data-repeater data-block-key="{{ $blockKey }}" data-locale="{{ $localeKey }}">
                                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                                    <strong class="small text-uppercase text-muted">{{ $definition['label'] ?? ucfirst(str_replace('_', ' ', $blockKey)) }}</strong>
                                                                    <button class="btn btn-sm btn-outline-primary" type="button" data-repeater-add>{{ __('Add item') }}</button>
                                                                </div>

                                                                <template data-repeater-template>
                                                                    <div class="repeater-item border rounded p-3 mb-3" data-repeater-item>
                                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                                            <strong class="small text-uppercase text-muted">{{ __('Item') }}</strong>
                                                                            <div class="btn-group btn-group-sm" role="group">
                                                                                <button class="btn btn-outline-secondary" type="button" data-repeater-up aria-label="{{ __('Move up') }}">&uarr;</button>
                                                                                <button class="btn btn-outline-secondary" type="button" data-repeater-down aria-label="{{ __('Move down') }}">&darr;</button>
                                                                                <button class="btn btn-outline-danger" type="button" data-repeater-remove>{{ __('Remove') }}</button>
                                                                            </div>
                                                                        </div>
                                                                        @include('cms::admin.cms.partials.repeater-fields', [
                                                                            'fields' => $definition['fields'] ?? [],
                                                                            'namePrefix' => "content[{$localeKey}][{$blockKey}][__INDEX__]",
                                                                            'values' => [],
                                                                        ])
                                                                    </div>
                                                                </template>

                                                                <div class="repeater-items" data-repeater-items>
                                                                    @forelse($items as $index => $item)
                                                                        <div class="repeater-item border rounded p-3 mb-3" data-repeater-item>
                                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                                <strong class="small text-uppercase text-muted">{{ __('Item') }} #{{ $loop->iteration }}</strong>
                                                                                <div class="btn-group btn-group-sm" role="group">
                                                                                    <button class="btn btn-outline-secondary" type="button" data-repeater-up aria-label="{{ __('Move up') }}">&uarr;</button>
                                                                                    <button class="btn btn-outline-secondary" type="button" data-repeater-down aria-label="{{ __('Move down') }}">&darr;</button>
                                                                                    <button class="btn btn-outline-danger" type="button" data-repeater-remove>{{ __('Remove') }}</button>
                                                                                </div>
                                                                            </div>
                                                                            @include('cms::admin.cms.partials.repeater-fields', [
                                                                                'fields' => $definition['fields'] ?? [],
                                                                                'namePrefix' => "content[{$localeKey}][{$blockKey}][{$index}]",
                                                                                'values' => $item,
                                                                            ])
                                                                        </div>
                                                                    @empty
                                                                        <p class="text-muted small" data-repeater-empty>{{ __('No items yet.') }}</p>
                                                                    @endforelse
                                                                </div>
                                                            </div>
                                                        @else
                                                            @include('cms::admin.cms.partials.repeater-fields', [
                                                                'fields' => $definition['fields'] ?? [],
                                                                'namePrefix' => "content[{$localeKey}][{$blockKey}]",
                                                                'values' => $blockValues[$blockKey] ?? [],
                                                            ])
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="editor-panel" data-editor-panel="seo" hidden>
                        @include('cms::admin.cms.seo', ['locales' => $locales, 'seo' => $seoData])
                    </div>

                    <div class="editor-panel" data-editor-panel="scripts" hidden>
                        @include('cms::admin.cms.scripts', ['locales' => $locales, 'scripts' => $scripts])
                    </div>

                    <div class="editor-panel" data-editor-panel="emails" hidden>
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Info email') }}</label>
                                        <input type="email" class="form-control" name="emails[info_email]" value="{{ $emails['info_email'] ?? '' }}" placeholder="info@example.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Notify email') }}</label>
                                        <input type="email" class="form-control" name="emails[notify_email]" value="{{ $emails['notify_email'] ?? '' }}" placeholder="notify@example.com">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
