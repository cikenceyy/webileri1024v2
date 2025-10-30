{{-- Cold rapor kartları ve son güncelleme rozeti bulunan yönetim ekranı. --}}
@extends('layouts.admin')

@section('title', 'Rapor Merkezi')
@section('module', 'Core')

@push('page-scripts')
    @vite('resources/js/pages/reports-index.js')
@endpush

@section('content')
    <div class="container-xl" data-report-center
         data-list-endpoint="{{ route('admin.reports.list') }}"
         data-refresh-endpoint="{{ route('admin.reports.refresh', ['reportKey' => '__KEY__']) }}"
         data-download-endpoint="{{ route('admin.reports.download', ['snapshot' => '__ID__']) }}"
         data-poll-interval="{{ $pollInterval }}">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h1 class="h3 mb-1">Rapor Merkezi</h1>
                <p class="text-muted mb-0">Ağır raporlar belirli aralıklarla hazırlanır; son güncelleme bilgisini kartlardan takip edin.</p>
            </div>
        </div>

        <div class="row g-4" data-report-grid>
            @foreach ($definitions as $definition)
                @php
                    $snapshot = $snapshots->firstWhere('report_key', $definition['key']);
                @endphp
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card shadow-sm report-card" data-report-card data-report-key="{{ $definition['key'] }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h2 class="h5 mb-1">{{ $definition['label'] }}</h2>
                                    <p class="text-muted mb-0">Anahtar: <code>{{ $definition['key'] }}</code></p>
                                </div>
                                <span class="badge bg-primary-subtle text-primary">{{ strtoupper($definition['module'] ?? 'core') }}</span>
                            </div>
                            <dl class="row small">
                                <dt class="col-6">Son güncelleme</dt>
                                <dd class="col-6 text-end" data-report-updated>{{ optional($snapshot?->generated_at)->diffForHumans() ?? 'hazırlanmadı' }}</dd>
                                <dt class="col-6">Geçerlilik</dt>
                                <dd class="col-6 text-end" data-report-valid>{{ optional($snapshot?->valid_until)->diffForHumans() ?? '—' }}</dd>
                                <dt class="col-6">Satır</dt>
                                <dd class="col-6 text-end" data-report-rows>{{ $snapshot?->rows ?? 0 }}</dd>
                                <dt class="col-6">Durum</dt>
                                <dd class="col-6 text-end" data-report-status>{{ $snapshot?->status ?? 'pending' }}</dd>
                            </dl>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                @if ($snapshot?->storage_path)
                                    <a href="{{ route('admin.reports.download', $snapshot) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       data-report-download>
                                        İndir
                                    </a>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled>İndir</button>
                                @endif
                                <button type="button"
                                        class="btn btn-sm btn-primary"
                                        data-report-refresh
                                        data-report-loading-text="Yenileniyor…">
                                    Yenile
                                </button>
                            </div>
                            <p class="text-muted mt-3 mb-0 small">Planlanan periyot: {{ $definition['schedule'] ?? '—' }} · TTL: {{ $definition['ttl'] ?? 3600 }} sn</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
