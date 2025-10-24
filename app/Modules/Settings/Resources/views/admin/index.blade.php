@extends('layouts.admin')

@section('title', __('Settings v2'))
@section('module', 'Settings')
@section('page', 'Settings v2')

@php
    $money = $settings->money;
    $tax = $settings->tax;
    $sequencing = $settings->sequencing;
    $defaults = $settings->defaults;
    $documents = $settings->documents;
    $general = $settings->general;

    $allowedCurrenciesInput = old('money.allowed_currencies');
    if (is_array($allowedCurrenciesInput)) {
        $allowedCurrenciesInput = implode(', ', $allowedCurrenciesInput);
    }
    if ($allowedCurrenciesInput === null) {
        $allowedCurrenciesInput = implode(', ', $money['allowed_currencies']);
    }

    $withholdingEnabled = (bool) old('tax.withholding_enabled', $tax['withholding_enabled']);
    $taxInclusive = (bool) old('defaults.tax_inclusive', $defaults['tax_inclusive']);
    $resetPolicy = old('sequencing.reset_policy', $sequencing['reset_policy']);
    $decimalPrecision = old('general.decimal_precision', $general['decimal_precision']);
@endphp

@section('content')
    <div class="container-fluid py-4">
        <x-ui-page-header
            title="Settings v2"
            description="{{ __('Şirket ayarlarını tek noktadan yönetin.') }}"
        >
            <div class="d-flex gap-3 align-items-center flex-wrap mt-2 text-muted small">
                <span>
                    {{ __('Son güncelleyen:') }}
                    <strong>{{ $meta['updated_by'] ?? __('Bilinmiyor') }}</strong>
                </span>
                <span>
                    {{ __('Güncellenme zamanı:') }}
                    <strong>{{ $meta['updated_at'] ? $meta['updated_at']->diffForHumans() : __('Henüz kaydedilmedi') }}</strong>
                </span>
            </div>

            <x-slot name="actions">
                <x-ui-badge variant="primary" tone="soft">
                    {{ __('Versiyon :version', ['version' => $meta['version'] ?? 1]) }}
                </x-ui-badge>
            </x-slot>
        </x-ui-page-header>

        <div class="mt-3">
            @if (session('status'))
                <x-ui-alert variant="success" dismissible class="mb-3">
                    {{ session('status') }}
                </x-ui-alert>
            @endif

            @if ($errors->any())
                <x-ui-alert variant="danger" dismissible class="mb-3">
                    {{ __('Kaydedilemedi. Lütfen işaretlenen alanları kontrol edin.') }}
                </x-ui-alert>
            @endif

            <x-ui-card>
                <form
                    method="POST"
                    action="{{ route('admin.settings.store') }}"
                    data-settings-form
                    data-original-reset-policy="{{ $sequencing['reset_policy'] }}"
                >
                    @csrf

                    <x-ui-tabs :tabs="[
                        ['id' => 'settings-money', 'label' => __('Para & Vergi')],
                        ['id' => 'settings-sequencing', 'label' => __('Numaralandırma')],
                        ['id' => 'settings-defaults', 'label' => __('Varsayılanlar')],
                        ['id' => 'settings-documents', 'label' => __('Belge Şablonları')],
                        ['id' => 'settings-general', 'label' => __('Genel')],
                    ]">
                        <div id="settings-money-panel" role="tabpanel" aria-labelledby="settings-money-tab" class="ui-tabs__panel is-active">
                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <x-ui-input
                                        name="money[base_currency]"
                                        label="{{ __('Temel Para Birimi') }}"
                                        value="{{ old('money.base_currency', $money['base_currency']) }}"
                                        maxlength="3"
                                        help="{{ __('ISO 4217 formatında, örn. USD') }}"
                                        class="text-uppercase"
                                    />
                                </div>
                                <div class="col-12 col-md-6">
                                    <x-ui-textarea
                                        name="money[allowed_currencies]"
                                        label="{{ __('İzin Verilen Para Birimleri') }}"
                                        rows="3"
                                        :value="$allowedCurrenciesInput"
                                        help="{{ __('Virgül veya satır sonu ile ayırın. Temel para birimini içermelidir.') }}"
                                    />
                                </div>
                                <div class="col-12 col-md-4">
                                    <x-ui-number
                                        name="tax[default_vat_rate]"
                                        label="{{ __('Varsayılan KDV Oranı (%)') }}"
                                        :value="old('tax.default_vat_rate', $tax['default_vat_rate'])"
                                        min="0"
                                        max="50"
                                        step="0.01"
                                    />
                                </div>
                                <div class="col-12 col-md-4 d-flex align-items-end">
                                    <div class="w-100">
                                        <input type="hidden" name="tax[withholding_enabled]" value="0">
                                        <x-ui-switch
                                            name="tax[withholding_enabled]"
                                            label="{{ __('Stopaj Aktif') }}"
                                            :checked="$withholdingEnabled"
                                            help="{{ __('Stopaj hesaplamalarını etkinleştirir.') }}"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="settings-sequencing-panel" role="tabpanel" aria-labelledby="settings-sequencing-tab" class="ui-tabs__panel" hidden>
                            <div class="row g-4">
                                <div class="col-12 col-md-4">
                                    <x-ui-input
                                        name="sequencing[invoice_prefix]"
                                        label="{{ __('Fatura Prefix') }}"
                                        value="{{ old('sequencing.invoice_prefix', $sequencing['invoice_prefix']) }}"
                                    />
                                </div>
                                <div class="col-12 col-md-4">
                                    <x-ui-input
                                        name="sequencing[order_prefix]"
                                        label="{{ __('Sipariş Prefix') }}"
                                        value="{{ old('sequencing.order_prefix', $sequencing['order_prefix']) }}"
                                    />
                                </div>
                                <div class="col-12 col-md-4">
                                    <x-ui-input
                                        name="sequencing[shipment_prefix]"
                                        label="{{ __('Sevkiyat Prefix') }}"
                                        value="{{ old('sequencing.shipment_prefix', $sequencing['shipment_prefix']) }}"
                                    />
                                </div>
                                <div class="col-12 col-md-4">
                                    <x-ui-number
                                        name="sequencing[padding]"
                                        label="{{ __('Numara Uzunluğu') }}"
                                        :value="old('sequencing.padding', $sequencing['padding'])"
                                        min="3"
                                        max="8"
                                    />
                                </div>
                                <div class="col-12 col-md-4">
                                    <x-ui-select
                                        name="sequencing[reset_policy]"
                                        label="{{ __('Sıfırlama Politikası') }}"
                                        :options="[
                                            ['value' => 'yearly', 'label' => __('Yıllık')],
                                            ['value' => 'never', 'label' => __('Hiçbir Zaman')],
                                        ]"
                                        :value="$resetPolicy"
                                        help="{{ __('Yıllık sıfırlama numaralandırmayı başa alır.') }}"
                                    />
                                </div>
                            </div>
                        </div>

                        <div id="settings-defaults-panel" role="tabpanel" aria-labelledby="settings-defaults-tab" class="ui-tabs__panel" hidden>
                            <div class="row g-4">
                                <div class="col-12 col-md-4">
                                    <x-ui-number
                                        name="defaults[payment_terms_days]"
                                        label="{{ __('Varsayılan Vade (gün)') }}"
                                        :value="old('defaults.payment_terms_days', $defaults['payment_terms_days'])"
                                        min="0"
                                        max="180"
                                    />
                                </div>
                                <div class="col-12 col-md-4">
                                    <x-ui-input
                                        name="defaults[warehouse_id]"
                                        label="{{ __('Varsayılan Depo ID') }}"
                                        value="{{ old('defaults.warehouse_id', $defaults['warehouse_id']) }}"
                                        help="{{ __('Tanımlı depo kimliğini girin veya boş bırakın.') }}"
                                    />
                                </div>
                                <div class="col-12 col-md-4">
                                    <x-ui-input
                                        name="defaults[price_list_id]"
                                        label="{{ __('Varsayılan Fiyat Listesi ID') }}"
                                        value="{{ old('defaults.price_list_id', $defaults['price_list_id']) }}"
                                        help="{{ __('Tanımlı fiyat listesi kimliğini girin veya boş bırakın.') }}"
                                    />
                                </div>
                                <div class="col-12 col-md-4 d-flex align-items-end">
                                    <div class="w-100">
                                        <input type="hidden" name="defaults[tax_inclusive]" value="0">
                                        <x-ui-switch
                                            name="defaults[tax_inclusive]"
                                            label="{{ __('Fiyatlar Vergi Dahil') }}"
                                            :checked="$taxInclusive"
                                            help="{{ __('Satış fiyatlarında vergiyi dahil kabul eder.') }}"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="settings-documents-panel" role="tabpanel" aria-labelledby="settings-documents-tab" class="ui-tabs__panel" hidden>
                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <x-ui-input
                                        name="documents[invoice_print_template]"
                                        label="{{ __('Fatura Şablon Kodu') }}"
                                        value="{{ old('documents.invoice_print_template', $documents['invoice_print_template']) }}"
                                        help="{{ __('Seçili baskı şablonunun kodu veya ID değeri.') }}"
                                    />
                                </div>
                                <div class="col-12 col-md-6">
                                    <x-ui-input
                                        name="documents[shipment_note_template]"
                                        label="{{ __('Sevkiyat Notu Şablon Kodu') }}"
                                        value="{{ old('documents.shipment_note_template', $documents['shipment_note_template']) }}"
                                    />
                                </div>
                                <div class="col-12">
                                    <x-ui-button
                                        type="button"
                                        variant="ghost"
                                        data-action="toast"
                                        data-title="{{ __('Önizleme') }}"
                                        data-message="{{ __('Önizleme yakında hazır olacak.') }}"
                                    >
                                        {{ __('Önizleme') }}
                                    </x-ui-button>
                                </div>
                            </div>
                        </div>

                        <div id="settings-general-panel" role="tabpanel" aria-labelledby="settings-general-tab" class="ui-tabs__panel" hidden>
                            <div class="row g-4">
                                <div class="col-12 col-md-4">
                                    <x-ui-input
                                        name="general[company_locale]"
                                        label="{{ __('Şirket Dili') }}"
                                        value="{{ old('general.company_locale', $general['company_locale']) }}"
                                        help="{{ __('Örn: tr_TR, en_US') }}"
                                    />
                                </div>
                                <div class="col-12 col-md-4">
                                    <x-ui-input
                                        name="general[timezone]"
                                        label="{{ __('Zaman Dilimi') }}"
                                        value="{{ old('general.timezone', $general['timezone']) }}"
                                        help="{{ __('IANA formatında, örn. Europe/Istanbul') }}"
                                    />
                                </div>
                                <div class="col-12 col-md-4">
                                    <x-ui-select
                                        name="general[decimal_precision]"
                                        label="{{ __('Ondalık Hassasiyeti') }}"
                                        :options="[
                                            ['value' => '2', 'label' => '2'],
                                            ['value' => '3', 'label' => '3'],
                                        ]"
                                        :value="(string) $decimalPrecision"
                                    />
                                </div>
                            </div>
                        </div>
                    </x-ui-tabs>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <x-ui-button type="reset" variant="ghost">{{ __('Geri Al') }}</x-ui-button>
                        </div>
                        <div class="d-flex gap-2">
                            <x-ui-button type="submit" variant="primary">{{ __('Kaydet') }}</x-ui-button>
                        </div>
                    </div>

                    <button type="button" data-action="open" data-target="#sequencing-confirm-modal" data-open-reset-modal hidden></button>
                </form>
            </x-ui-card>
        </div>
    </div>

    <x-ui-modal id="sequencing-confirm-modal" title="{{ __('Numaralandırma politikası değişiyor') }}">
        <p class="mb-3">{{ __('Numaralandırma sıfırlama politikasını değiştirmek mevcut sıra numaralarını etkileyebilir. Devam etmek istediğinize emin misiniz?') }}</p>

        <x-slot name="footer">
            <div class="d-flex justify-content-end gap-2 w-100">
                <x-ui-button type="button" variant="ghost" data-action="close" data-modal-close>{{ __('Vazgeç') }}</x-ui-button>
                <x-ui-button type="button" variant="primary" data-confirm-reset-policy>{{ __('Onayla ve Kaydet') }}</x-ui-button>
            </div>
        </x-slot>
    </x-ui-modal>
@endsection

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-settings-form]');
            if (!form) {
                return;
            }

            const resetPolicyField = form.querySelector('[name="sequencing[reset_policy]"]');
            const originalResetPolicy = form.dataset.originalResetPolicy || (resetPolicyField ? resetPolicyField.value : '');
            const trigger = form.querySelector('[data-open-reset-modal]');
            const modal = document.getElementById('sequencing-confirm-modal');
            const closeControl = modal ? modal.querySelector('[data-modal-close]') : null;
            const confirmButton = modal ? modal.querySelector('[data-confirm-reset-policy]') : null;

            form.addEventListener('reset', () => {
                window.requestAnimationFrame(() => {
                    if (resetPolicyField && originalResetPolicy) {
                        resetPolicyField.value = originalResetPolicy;
                    }
                    delete form.dataset.resetConfirmed;
                });
            });

            form.addEventListener('submit', (event) => {
                if (!resetPolicyField || !originalResetPolicy) {
                    return;
                }

                if (form.dataset.resetConfirmed === 'true') {
                    delete form.dataset.resetConfirmed;
                    return;
                }

                const nextValue = resetPolicyField.value;
                if (nextValue !== originalResetPolicy) {
                    event.preventDefault();
                    if (trigger) {
                        trigger.click();
                    }
                }
            });

            confirmButton?.addEventListener('click', () => {
                form.dataset.resetConfirmed = 'true';
                closeControl?.click();
                window.setTimeout(() => {
                    form.requestSubmit();
                }, 120);
            });
        });
    </script>
@endpush
