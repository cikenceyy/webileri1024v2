{{-- Admin > Ayarlar > E-posta Merkezi sayfası: x/y adresleri, politika ve test gönderimleri. --}}
@extends('layouts.admin')

@section('title', __('E-posta Merkezi'))
@section('module', 'Settings')
@section('page', __('E-posta Merkezi'))

@section('content')
    <div
        class="container-fluid py-4"
        data-settings-email
        data-update-url="{{ route('admin.settings.email.update') }}"
        data-test-url="{{ route('admin.settings.email.test') }}"
        data-csrf="{{ csrf_token() }}"
    >
        <x-ui-page-header
            title="{{ __('E-posta Merkezi') }}"
            description="{{ __('Çıkış adreslerini, teslim politikalarını ve marka bilgilerini tek yerden yönetin.') }}"
        ></x-ui-page-header>

        <div class="row g-4 mt-1">
            <div class="col-12 col-lg-8">
                <x-ui-card>
                    <form class="vstack gap-3" data-email-form>
                        @csrf
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="email_outbound_x" class="form-label fw-semibold">{{ __('Ana Adres (x@)') }}</label>
                                <input type="email" class="form-control" id="email_outbound_x" name="email_outbound_x" value="{{ $values['email.outbound.x'] }}" placeholder="x@firma.com">
                            </div>
                            <div class="col-sm-6">
                                <label for="email_outbound_y" class="form-label fw-semibold">{{ __('İkincil Adres (y@)') }}</label>
                                <input type="email" class="form-control" id="email_outbound_y" name="email_outbound_y" value="{{ $values['email.outbound.y'] }}" placeholder="y@firma.com">
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="email_policy_deliver_to" class="form-label fw-semibold">{{ __('Teslim Politikası') }}</label>
                                <select class="form-select" id="email_policy_deliver_to" name="email_policy_deliver_to">
                                    <option value="both" @selected($values['email.policy.deliver_to'] === 'both')>{{ __('Her iki adres') }}</option>
                                    <option value="x_only" @selected($values['email.policy.deliver_to'] === 'x_only')>{{ __('Yalnız x@') }}</option>
                                    <option value="y_only" @selected($values['email.policy.deliver_to'] === 'y_only')>{{ __('Yalnız y@') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="email_policy_from" class="form-label fw-semibold">{{ __('Gönderici Kimliği') }}</label>
                                <select class="form-select" id="email_policy_from" name="email_policy_from">
                                    <option value="system" @selected($values['email.policy.from'] === 'system')>{{ __('Sistem varsayılanı') }}</option>
                                    <option value="x" @selected($values['email.policy.from'] === 'x')>{{ __('x@ adresinden') }}</option>
                                    <option value="y" @selected($values['email.policy.from'] === 'y')>{{ __('y@ adresinden') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="email_policy_reply_to" class="form-label fw-semibold">{{ __('Yanıt Adresi') }}</label>
                                <select class="form-select" id="email_policy_reply_to" name="email_policy_reply_to">
                                    <option value="x" @selected(($values['email.policy.reply_to'] ?? 'x') === 'x')>{{ __('x@') }}</option>
                                    <option value="y" @selected(($values['email.policy.reply_to'] ?? '') === 'y')>{{ __('y@') }}</option>
                                    <option value="none" @selected(($values['email.policy.reply_to'] ?? '') === '')>{{ __('Yanıt alma') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="email_brand_name" class="form-label fw-semibold">{{ __('Gönderici Adı') }}</label>
                                <input type="text" class="form-control" id="email_brand_name" name="email_brand_name" value="{{ $values['email.brand.name'] }}" placeholder="Firma / Marka adı">
                            </div>
                            <div class="col-sm-6">
                                <label for="email_brand_address" class="form-label fw-semibold">{{ __('Gönderici Adresi (opsiyonel)') }}</label>
                                <input type="email" class="form-control" id="email_brand_address" name="email_brand_address" value="{{ $values['email.brand.address'] }}" placeholder="noreply@firma.com">
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <button type="button" class="btn btn-outline-secondary" data-email-test>
                                <span class="spinner-border spinner-border-sm align-middle me-2 d-none" role="status"></span>
                                {{ __('Kendime Deneme Gönder') }}
                            </button>

                            <button type="submit" class="btn btn-primary" data-email-submit>
                                <span class="spinner-border spinner-border-sm align-middle me-2 d-none" role="status"></span>
                                {{ __('Kaydet') }}
                            </button>
                        </div>
                    </form>
                </x-ui-card>
            </div>

            <div class="col-12 col-lg-4">
                <x-ui-card>
                    <header class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h6 mb-0">{{ __('Son 10 Gönderim') }}</h2>
                        <span class="badge text-bg-light">{{ $logs->count() }}</span>
                    </header>
                    <ul class="list-group" data-email-log aria-live="polite">
                        @forelse($logs as $log)
                            <li class="list-group-item d-flex flex-column gap-1">
                                <span class="fw-semibold">{{ strtoupper($log->status) }} • {{ $log->subject ?? __('Belirtilmedi') }}</span>
                                <span class="text-muted small">{{ $log->created_at?->diffForHumans() }} • {{ collect($log->recipients['to'] ?? [])->implode(', ') }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">{{ __('Henüz kayıt yok.') }}</li>
                        @endforelse
                    </ul>
                    <x-ui-alert variant="warning" tone="soft" icon="bi bi-exclamation-triangle" class="mt-3">
                        {{ __('Test gönderimi yalnız bu oturumun e-posta adresine gider ve yönlendirme kurallarını etkilemez.') }}
                    </x-ui-alert>
                </x-ui-card>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    @vite('resources/js/pages/settings-email.js')
@endpush
