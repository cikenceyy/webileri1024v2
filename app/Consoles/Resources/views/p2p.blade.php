@extends('layouts.admin')

@php($module = 'Consoles')
@php($page = 'p2p')
@php($hasFilters = ! empty(array_filter($filters ?? [])))

@section('content')
    <div class="container-fluid py-4 console-page">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 mb-2">Procure-to-Pay Konsolu</h1>
                <p class="text-muted mb-0">Satın alma taleplerinden tedarikçi ödemesine kadar süreci tek bakışta izleyin.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <span class="text-uppercase small text-muted fw-semibold">Filtreler</span>
                @if($hasFilters)
                    <a class="btn btn-link btn-sm text-decoration-none" href="{{ route('consoles.p2p') }}">Temizle</a>
                @endif
            </div>
            <div class="card-body">
                <form method="get" class="row g-3 align-items-end" action="{{ route('consoles.p2p') }}">
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label" for="status">Durum</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tümü</option>
                            <option value="draft" @selected(($filters['status'] ?? null) === 'draft')>Taslak</option>
                            <option value="approved" @selected(($filters['status'] ?? null) === 'approved')>Onaylandı</option>
                            <option value="received" @selected(($filters['status'] ?? null) === 'received')>Alındı</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label" for="supplier_id">Tedarikçi</label>
                        <input class="form-control" type="number" id="supplier_id" name="supplier_id" value="{{ $filters['supplier_id'] ?? '' }}" min="1" placeholder="Tedarikçi ID">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="search">Ara</label>
                        <input class="form-control" type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="PO no / referans">
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label" for="from">Başlangıç</label>
                        <input class="form-control" type="date" id="from" name="from" value="{{ $filters['from'] ?? '' }}">
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label" for="to">Bitiş</label>
                        <input class="form-control" type="date" id="to" name="to" value="{{ $filters['to'] ?? '' }}">
                    </div>
                    <div class="col-12 col-md-2">
                        <button class="btn btn-primary w-100" type="submit">Filtrele</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
            <div class="col">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted fw-semibold">Açık PO</span>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['kpis']['open_pos'] ?? 0) }}</span>
                            <span class="text-muted small">adet</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted fw-semibold">Teslim Bekleyen</span>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['kpis']['awaiting_receipt'] ?? 0) }}</span>
                            <span class="text-muted small">sevkiyat</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted fw-semibold">Kabul Kuyruğu</span>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['kpis']['pending_grn'] ?? 0) }}</span>
                            <span class="text-muted small">işlem</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted fw-semibold">Yaklaşan Ödemeler</span>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['kpis']['ap_due'] ?? 0) }}</span>
                            <span class="text-muted small">fatura</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php($actionFields = [
            'po.approve' => 'purchase_order_id',
            'grn.receive' => 'grn_id',
            'ap.invoice.post' => 'purchase_order_id',
            'ap.payment.register' => 'ap_invoice_id',
        ])

        <div class="row g-4">
            @foreach($state['pipeline'] ?? [] as $stage)
                @php($rows = $stage['rows'] ?? [])
                <div class="col-12 col-xl-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h2 class="h6 mb-1">{{ $stage['label'] ?? 'Aksiyon' }}</h2>
                                    <p class="text-muted small mb-0">Sürecin bu adımında bekleyen kayıtların öncelikli özeti.</p>
                                </div>
                                <span class="badge text-bg-primary rounded-pill">{{ number_format($stage['count'] ?? 0) }}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(!empty($rows))
                                <div class="list-group list-group-flush">
                                    @foreach($rows as $row)
                                        @php($field = $actionFields[$stage['action']] ?? null)
                                        <div class="list-group-item px-0 border-0 py-3">
                                            <div class="d-flex flex-column gap-3">
                                                <div class="d-flex justify-content-between align-items-start gap-3">
                                                    <div>
                                                        <div class="fw-semibold">{{ $row['reference'] ?? ('PO-' . ($row['id'] ?? '')) }}</div>
                                                        <div class="text-muted small">{{ $row['supplier_id'] ? 'Tedarikçi #' . $row['supplier_id'] : 'Tedarikçi bilgisi yok' }}</div>
                                                    </div>
                                                    <span class="badge text-bg-light text-capitalize">{{ str($row['status'] ?? '')->replace('_', ' ')->headline() ?: '—' }}</span>
                                                </div>
                                                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between align-items-sm-center">
                                                    <div class="text-muted small">
                                                        Toplam: {{ isset($row['total']) ? number_format($row['total'], 2) : (isset($row['balance_due']) ? number_format($row['balance_due'], 2) : '—') }}
                                                    </div>
                                                    <form method="post" action="{{ route('consoles.p2p.execute', $stage['action']) }}" class="text-sm-end">
                                                        @csrf
                                                        @if($field && isset($row['id']))
                                                            <input type="hidden" name="{{ $field }}" value="{{ $row['id'] }}">
                                                        @endif
                                                        <button class="btn btn-sm btn-outline-primary" type="submit">Aksiyonu Çalıştır</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <p class="mb-1 fw-semibold">Bekleyen kayıt yok</p>
                                    <p class="mb-0 small">Tüm işlemler bu adım için güncel.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
