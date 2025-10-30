{{-- Admin > Ayarlar > Modül ayarları sayfası: Drive, Envanter, Finans ve CMS yapılandırmaları. --}}
@extends('layouts.admin')

@section('title', __('Modül Ayarları'))
@section('module', 'Settings')
@section('page', __('Modül Ayarları'))

@section('content')
    <div
        class="container-fluid py-4"
        data-settings-modules
        data-update-url="{{ route('admin.settings.modules.update') }}"
        data-csrf="{{ csrf_token() }}"
    >
        <x-ui-page-header
            title="{{ __('Modül Ayarları') }}"
            description="{{ __('Drive, Envanter, Finans ve CMS modülleri için tip güvenli yapılandırmaları düzenleyin.') }}"
        ></x-ui-page-header>

        <x-ui-card>
            <form class="vstack gap-4" data-modules-form>
                @csrf
                <section aria-labelledby="driveSettings">
                    <header class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h2 id="driveSettings" class="h6 mb-1">{{ __('Drive') }}</h2>
                            <p class="text-muted mb-0">{{ __('Versiyonlama ve hızlı paylaşım politikaları') }}</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="drive_enable_versioning" name="drive_enable_versioning" value="1" @checked($values['drive_enable_versioning'])>
                            <label class="form-check-label" for="drive_enable_versioning">{{ __('Dosya versiyonlamasını aktifleştir') }}</label>
                        </div>
                    </header>
                    <x-ui-alert variant="info" tone="soft" icon="bi bi-shield-check" class="mb-0">
                        {{ __('Versiyonlama aktifken, eski dosyalar kısa süreli önbellekten otomatik temizlenir.') }}
                    </x-ui-alert>
                </section>

                <section aria-labelledby="inventorySettings">
                    <header class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h2 id="inventorySettings" class="h6 mb-1">{{ __('Envanter') }}</h2>
                            <p class="text-muted mb-0">{{ __('Stok eşikleri ve uyarı limitleri') }}</p>
                        </div>
                    </header>
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-4">
                            <label for="inventory_low_stock_threshold" class="form-label fw-semibold">{{ __('Düşük Stok Eşiği') }}</label>
                            <input type="number" min="0" class="form-control" id="inventory_low_stock_threshold" name="inventory_low_stock_threshold" value="{{ $values['inventory_low_stock_threshold'] }}">
                            <div class="form-text">{{ __('Bu eşiğin altındaki ürünler uyarı listesine düşer.') }}</div>
                        </div>
                    </div>
                </section>

                <section aria-labelledby="financeSettings">
                    <header class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h2 id="financeSettings" class="h6 mb-1">{{ __('Finans') }}</h2>
                            <p class="text-muted mb-0">{{ __('Para birimi ve varsayılan formatlar') }}</p>
                        </div>
                    </header>
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-4">
                            <label for="finance_default_currency" class="form-label fw-semibold">{{ __('Varsayılan Para Birimi') }}</label>
                            <input type="text" class="form-control text-uppercase" id="finance_default_currency" name="finance_default_currency" value="{{ $values['finance_default_currency'] }}" maxlength="3">
                        </div>
                    </div>
                </section>

                <section aria-labelledby="cmsSettings">
                    <header class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h2 id="cmsSettings" class="h6 mb-1">{{ __('CMS') }}</h2>
                            <p class="text-muted mb-0">{{ __('Özel özellikler ve deneysel bileşenler') }}</p>
                        </div>
                    </header>
                    <div class="mb-2">
                        <label for="cms_feature_flags" class="form-label fw-semibold">{{ __('Özellik Bayrakları (JSON)') }}</label>
                        <textarea id="cms_feature_flags" name="cms_feature_flags" rows="6" class="form-control font-monospace" aria-describedby="cmsFeatureFlagsHelp">{{ $values['cms_feature_flags'] }}</textarea>
                        <div id="cmsFeatureFlagsHelp" class="form-text">{{ __('Örnek: {"contact_form": true, "cta_banner": false}') }}</div>
                    </div>
                </section>

                <x-ui-alert variant="warning" tone="soft" icon="bi bi-lightning" class="mb-0">
                    {{ __('Kritik modül değişiklikleri sonrası önbellek otomatik temizlenir ve ısıtıcılar tetiklenir.') }}
                </x-ui-alert>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary" data-modules-submit>
                        <span class="spinner-border spinner-border-sm align-middle me-2 d-none" role="status"></span>
                        {{ __('Kaydet') }}
                    </button>
                </div>
            </form>
        </x-ui-card>
    </div>
@endsection

@push('page-scripts')
    @vite('resources/js/pages/settings-modules.js')
@endpush
