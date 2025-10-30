{{-- Admin > Ayarlar > Genel bilgiler sayfası: firma adı, dil, saat dilimi ve logo yönetimi. --}}
@extends('layouts.admin')

@section('title', __('Genel Ayarlar'))
@section('module', 'Settings')
@section('page', __('Genel Ayarlar'))

@section('content')
    <div
        class="container-fluid py-4"
        data-settings-general
        data-update-url="{{ route('admin.settings.general.update') }}"
        data-csrf="{{ csrf_token() }}"
    >
        <x-ui-page-header
            title="{{ __('Genel Ayarlar') }}"
            description="{{ __('Firma adı, dil ve saat dilimi gibi temel bilgileri merkezi olarak güncelleyin.') }}"
        ></x-ui-page-header>

        <div class="row g-4 mt-1">
            <div class="col-12 col-lg-8">
                <x-ui-card>
                    <form class="vstack gap-3" data-general-form enctype="multipart/form-data">
                        @csrf
                        <div>
                            <label for="company_name" class="form-label fw-semibold">{{ __('Firma Adı') }}</label>
                            <input
                                type="text"
                                id="company_name"
                                name="company_name"
                                value="{{ $values['name'] }}"
                                class="form-control"
                                required
                                maxlength="255"
                                aria-describedby="companyNameHelp"
                            >
                            <div id="companyNameHelp" class="form-text">{{ __('Müşteri ve dokümanlarda görünecek ticari unvan.') }}</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="company_locale" class="form-label fw-semibold">{{ __('Dil') }}</label>
                                <select id="company_locale" name="company_locale" class="form-select" aria-label="{{ __('Dil seçimi') }}">
                                    @foreach($options['languages'] as $language)
                                        <option value="{{ $language['code'] }}" @selected($values['locale'] === $language['code'])>
                                            {{ $language['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label for="company_timezone" class="form-label fw-semibold">{{ __('Saat Dilimi') }}</label>
                                <select id="company_timezone" name="company_timezone" class="form-select" data-live-search="true">
                                    @foreach($options['timezones'] as $timezone)
                                        <option value="{{ $timezone['value'] }}" @selected($values['timezone'] === $timezone['value'])>
                                            {{ $timezone['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="company_logo" class="form-label fw-semibold">{{ __('Logo Yükle') }}</label>
                            <input
                                type="file"
                                class="form-control"
                                id="company_logo"
                                name="company_logo"
                                accept="image/*"
                                aria-describedby="companyLogoHelp"
                            >
                            <div id="companyLogoHelp" class="form-text">{{ __('PNG veya SVG, maksimum 2 MB.') }}</div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <div class="border rounded p-3 bg-light flex-grow-1" role="status" aria-live="polite">
                                <h3 class="h6 mb-2">{{ __('Aktif Logo Önizleme') }}</h3>
                                <div data-logo-preview class="ratio ratio-4x1 border rounded bg-white d-flex align-items-center justify-content-center overflow-hidden">
                                    @if($values['logo_url'])
                                        <img src="{{ $values['logo_url'] }}" alt="{{ __('Firma logosu') }}" class="img-fluid">
                                    @else
                                        <span class="text-muted">{{ __('Logo yüklenmemiş.') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary" data-general-submit>
                                    <span class="spinner-border spinner-border-sm align-middle me-2 d-none" role="status"></span>
                                    <span>{{ __('Kaydet') }}</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </x-ui-card>
            </div>

            <div class="col-12 col-lg-4">
                <x-ui-card>
                    <h2 class="h6 mb-3">{{ __('Durum Özeti') }}</h2>
                    <ul class="list-group" aria-live="polite">
                        <li class="list-group-item d-flex flex-column">
                            <span class="fw-semibold">{{ __('Son Güncelleyen') }}</span>
                            <span class="text-muted small" data-general-updated-by>{{ $meta['updated_by'] ?? __('Kayıt yok') }}</span>
                        </li>
                        <li class="list-group-item d-flex flex-column">
                            <span class="fw-semibold">{{ __('Son Güncelleme Zamanı') }}</span>
                            <span class="text-muted small" data-general-updated-at>
                                @if($meta['updated_at'])
                                    {{ $meta['updated_at']->diffForHumans() }}
                                @else
                                    {{ __('Kayıt yok') }}
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item">
                            <x-ui-alert variant="info" tone="soft" icon="bi bi-info-circle" class="mb-0">
                                {{ __('Logo değişikliklerinden sonra önbellek otomatik temizlenir ve menüler yeniden ısınır.') }}
                            </x-ui-alert>
                        </li>
                    </ul>
                </x-ui-card>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    @vite('resources/js/pages/settings-general.js')
@endpush
