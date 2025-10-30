{{-- Admin > Ayarlar > Önbellek Yönetimi sayfası: ısıtma/temizlik aksiyonlarının UI şeması. --}}
@extends('layouts.admin')

@section('title', __('Önbellek Yönetimi'))
@section('module', 'Settings')
@section('page', __('Önbellek Yönetimi'))

@section('content')
    <div
        class="container-fluid py-4"
        data-cache-page
        data-warm-url="{{ route('admin.settings.cache.warm') }}"
        data-flush-url="{{ route('admin.settings.cache.flush') }}"
        data-csrf="{{ csrf_token() }}"
        data-store="{{ $cacheStore }}"
        data-prefix="{{ $cachePrefix }}"
        data-last-warm="{{ $meta['last_warm']?->toIso8601String() }}"
        data-last-flush="{{ $meta['last_flush']?->toIso8601String() }}"
    >
        <x-ui-page-header
            title="{{ __('Önbellek Yönetimi') }}"
            description="{{ __('Tenant bazlı önbellekleri tek ekrandan izleyin, ısıtın ve temizleyin.') }}"
        >
            <x-slot name="actions">
                <div class="d-flex flex-wrap gap-2" role="toolbar" aria-label="{{ __('Hızlı aksiyonlar') }}">
                    <x-ui-button
                        type="button"
                        variant="primary"
                        data-cache-action="warm-all"
                    >
                        {{ __('Tümünü Isıt (Bu Firma)') }}
                    </x-ui-button>
                    <x-ui-button
                        type="button"
                        variant="danger"
                        tone="soft"
                        data-cache-action="flush-all"
                    >
                        {{ __('Tümünü Temizle (Bu Firma)') }}
                    </x-ui-button>
                </div>
            </x-slot>
        </x-ui-page-header>

        <div class="row g-4 mt-1">
            <div class="col-12 col-lg-8">
                <x-ui-card>
                    <header class="d-flex flex-column flex-md-row justify-content-between gap-3">
                        <div>
                            <h2 class="h5 mb-1">{{ __('Seçili Alanı Yönet') }}</h2>
                            <p class="text-muted mb-0">{{ __('Menü, yan panel, gösterge paneli veya Drive listelerini hedefleyin.') }}</p>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <label class="visually-hidden" for="cacheEntitySelect">{{ __('Önbellek alanı') }}</label>
                            <select
                                id="cacheEntitySelect"
                                class="form-select"
                                data-cache-entity
                                aria-label="{{ __('Önbellek alanı seçimi') }}"
                            >
                                <option value="menu">{{ __('Menü') }}</option>
                                <option value="sidebar">{{ __('Yan Menü') }}</option>
                                <option value="dashboard">{{ __('Gösterge Paneli') }}</option>
                                <option value="drive">{{ __('Drive Listeleri') }}</option>
                            </select>
                            <div class="btn-group" role="group" aria-label="{{ __('Seçim aksiyonları') }}">
                                <button type="button" class="btn btn-outline-primary" data-cache-action="warm-selected">
                                    {{ __('Seçileni Isıt') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-cache-action="flush-selected">
                                    {{ __('Seçileni Temizle') }}
                                </button>
                            </div>
                        </div>
                    </header>

                    <x-ui-alert variant="warning" tone="soft" class="mt-3" icon="bi bi-exclamation-triangle">
                        {{ __('Temizlik sonrası ilk istekte veriler tazelenirken birkaç saniyelik yavaşlama olabilir. Kritik akışlardan önce ısıtma aksiyonunu çalıştırmanız önerilir.') }}
                    </x-ui-alert>

                    <div class="row g-3 mt-3" data-cache-status>
                        <div class="col-sm-6 col-lg-3">
                            <div class="border rounded p-3 h-100" role="status" aria-live="polite">
                                <h3 class="h6 mb-1">{{ __('Aktif Store') }}</h3>
                                <p class="mb-0 text-muted" data-cache-stat="store">{{ strtoupper($cacheStore) }}</p>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="border rounded p-3 h-100" role="status">
                                <h3 class="h6 mb-1">{{ __('Prefix') }}</h3>
                                <p class="mb-0 text-muted" data-cache-stat="prefix">{{ $cachePrefix }}</p>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="border rounded p-3 h-100" role="status">
                                <h3 class="h6 mb-1">{{ __('Son Isıtma') }}</h3>
                                <p class="mb-0 text-muted" data-cache-stat="last_warm">
                                    @if($meta['last_warm'])
                                        {{ $meta['last_warm']->diffForHumans() }}
                                    @else
                                        {{ __('Kayıt yok') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="border rounded p-3 h-100" role="status">
                                <h3 class="h6 mb-1">{{ __('Son Temizlik') }}</h3>
                                <p class="mb-0 text-muted" data-cache-stat="last_flush">
                                    @if($meta['last_flush'])
                                        {{ $meta['last_flush']->diffForHumans() }}
                                    @else
                                        {{ __('Kayıt yok') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <section class="mt-4">
                        <h3 class="h6 mb-2">{{ __('TTL Profilleri') }}</h3>
                        <div class="row g-3">
                            @foreach($ttlProfiles as $name => $seconds)
                                <div class="col-6 col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <span class="d-block text-muted text-uppercase small">{{ $name }}</span>
                                        <strong class="d-block">{{ $seconds }} {{ __('sn') }}</strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </x-ui-card>
            </div>

            <div class="col-12 col-lg-4">
                <x-ui-card>
                    <header class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 mb-0">{{ __('Güncel Log Kaydı') }}</h2>
                        <span class="badge text-bg-light" data-cache-event-count>{{ count($events) }}</span>
                    </header>
                    <ul class="list-group" data-cache-events aria-live="polite">
                        @forelse($events as $event)
                            <li class="list-group-item d-flex flex-column gap-1">
                                <span class="fw-semibold">{{ strtoupper($event['action']) }} @if($event['context'])<small class="text-muted">{{ implode(', ', array_map(fn($value, $key) => is_array($value) ? $key . ':' . implode('|', $value) : $key . ':' . $value, $event['context'], array_keys($event['context']))) }}</small>@endif</span>
                                <span class="text-muted small">{{ $event['timestamp']->diffForHumans() }} • {{ __('Store: :store', ['store' => $event['store']]) }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">{{ __('Henüz log kaydı bulunmuyor.') }}</li>
                        @endforelse
                    </ul>
                </x-ui-card>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    @vite('resources/js/pages/settings-cache.js')
@endpush
