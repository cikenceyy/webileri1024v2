@extends('layouts.admin')

@section('title', __('Domain Tanılama'))
@section('module', 'Settings')
@section('page', 'Domain Tanılama')

@php
    $data = $data ?? [];
    $company = $data['company'] ?? null;
    $events = $data['events'] ?? [];
@endphp

@section('content')
    <div class="container-fluid py-4" data-page="settings-domain-diagnostics">
        <x-ui-page-header
            title="{{ __('Domain Tanılama') }}"
            description="{{ __('Bu ekran host → şirket eşleşmesini sadece görüntüler, değişiklik için CLI kullanılır.') }}"
        >
            <x-slot name="actions">
                <x-ui-badge variant="primary" tone="outline">
                    {{ $data['cloud_enabled'] ? __('Cloud Modu Açık') : __('Local Modu') }}
                </x-ui-badge>
            </x-slot>
        </x-ui-page-header>

        <div class="alert alert-warning d-flex align-items-center gap-2 mt-4" role="alert">
            <i class="bi bi-shield-lock" aria-hidden="true"></i>
            <span>{{ __('Domain değişimi yalnızca superadmin/CLI ile yapılır. Bu ekran salt okunurdur.') }}</span>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-12 col-lg-4">
                <x-ui-card aria-labelledby="diagnostics-host-title">
                    <div class="d-flex flex-column gap-2">
                        <div>
                            <h2 id="diagnostics-host-title" class="h5 mb-1">{{ __('Aktif Host & Şirket') }}</h2>
                            <p class="text-muted small mb-0">
                                {{ __('Orijinal host ve normalize edilmiş hali aşağıda listelenir.') }}
                            </p>
                        </div>
                        <dl class="row mb-0">
                            <dt class="col-5 text-muted">{{ __('Host') }}</dt>
                            <dd class="col-7">{{ $data['host'] ?? '—' }}</dd>
                            <dt class="col-5 text-muted">{{ __('Normalize Host') }}</dt>
                            <dd class="col-7">{{ $data['normalized_host'] ?? '—' }}</dd>
                            <dt class="col-5 text-muted">{{ __('Şirket') }}</dt>
                            <dd class="col-7">
                                @if ($company)
                                    <span class="d-block fw-semibold">{{ $company['name'] }}</span>
                                    <span class="text-muted small">ID: {{ $company['id'] }}</span>
                                @else
                                    —
                                @endif
                            </dd>
                            <dt class="col-5 text-muted">{{ __('Domain ID') }}</dt>
                            <dd class="col-7">{{ $data['domain_id'] ?? '—' }}</dd>
                            <dt class="col-5 text-muted">{{ __('Den. Edilen Hostlar') }}</dt>
                            <dd class="col-7 small text-break">
                                @if (! empty($data['hosts_tried']))
                                    {{ implode(', ', $data['hosts_tried']) }}
                                @else
                                    —
                                @endif
                            </dd>
                        </dl>
                    </div>
                </x-ui-card>
            </div>
            <div class="col-12 col-lg-4">
                <x-ui-card aria-labelledby="diagnostics-cache-title">
                    <div class="d-flex flex-column gap-2">
                        <div>
                            <h2 id="diagnostics-cache-title" class="h5 mb-1">{{ __('Domain Cache Durumu') }}</h2>
                            <p class="text-muted small mb-0">
                                {{ __('Çözümleme kaynağı, TTL ve fallback bilgileri.') }}
                            </p>
                        </div>
                        <dl class="row mb-0">
                            <dt class="col-5 text-muted">{{ __('Kaynak') }}</dt>
                            <dd class="col-7 text-uppercase">{{ $data['source'] ?? '—' }}</dd>
                            <dt class="col-5 text-muted">{{ __('Cache Hit?') }}</dt>
                            <dd class="col-7">
                                <span class="badge {{ ($data['cache_hit'] ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ($data['cache_hit'] ?? false) ? __('Evet') : __('Hayır') }}
                                </span>
                            </dd>
                            <dt class="col-5 text-muted">{{ __('TTL (sn)') }}</dt>
                            <dd class="col-7">{{ $data['cache_ttl'] ?? '—' }}</dd>
                            <dt class="col-5 text-muted">{{ __('Fallback Kullanıldı mı?') }}</dt>
                            <dd class="col-7">{{ ($data['fallback_used'] ?? false) ? __('Evet') : __('Hayır') }}</dd>
                            <dt class="col-5 text-muted">{{ __('Son Domain Flush') }}</dt>
                            <dd class="col-7">{{ optional($data['last_domain_flush'])->diffForHumans() ?? '—' }}</dd>
                            <dt class="col-5 text-muted">{{ __('Fallback Şirket ID') }}</dt>
                            <dd class="col-7">{{ $data['local_fallback_company_id'] ?? '—' }}</dd>
                        </dl>
                    </div>
                </x-ui-card>
            </div>
            <div class="col-12 col-lg-4">
                <x-ui-card aria-labelledby="diagnostics-events-title">
                    <div class="d-flex flex-column gap-2">
                        <div>
                            <h2 id="diagnostics-events-title" class="h5 mb-1">{{ __('Son İşlemler') }}</h2>
                            <p class="text-muted small mb-0">
                                {{ __('En güncel domain flush işlemleri listelenir (10 kayıt).') }}
                            </p>
                        </div>
                        @if ($events === [])
                            <p class="text-muted small mb-0">{{ __('Kayıt bulunamadı.') }}</p>
                        @else
                            <ul class="list-unstyled mb-0" aria-live="polite">
                                @foreach ($events as $event)
                                    <li class="mb-3">
                                        <div class="fw-semibold">{{ \Illuminate\Support\Str::of($event['action'])->replace('.', ' → ')->upper() }}</div>
                                        <div class="text-muted small">
                                            {{ __('Şirket ID: :id', ['id' => $event['company_id'] ?? '—']) }} ·
                                            {{ $event['timestamp'] }}
                                        </div>
                                        @if (! empty($event['domains']))
                                            <div class="small text-break">{{ implode(', ', $event['domains']) }}</div>
                                        @endif
                                        @if (! empty($event['reason']))
                                            <div class="text-muted small">{{ __('Sebep: :reason', ['reason' => $event['reason']]) }}</div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </x-ui-card>
            </div>
        </div>
    </div>
@endsection
