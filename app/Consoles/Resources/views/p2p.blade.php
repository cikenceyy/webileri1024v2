@extends('layouts.admin')

@php($module = 'Consoles')
@php($page = 'p2p')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="h4 mb-1">Procure-to-Pay Konsolu</h1>
                <p class="text-muted mb-0">Satın alma taleplerinden tedarikçi ödemesine kadar süreci izleyin.</p>
            </div>
            <form method="get" class="row g-2 align-items-end" action="{{ route('consoles.p2p') }}">
                <div class="col-auto">
                    <label class="form-label mb-1" for="status">Durum</label>
                    <select class="form-select form-select-sm" id="status" name="status">
                        <option value="">Tümü</option>
                        <option value="draft" @selected(($filters['status'] ?? null) === 'draft')>Taslak</option>
                        <option value="approved" @selected(($filters['status'] ?? null) === 'approved')>Onaylandı</option>
                        <option value="received" @selected(($filters['status'] ?? null) === 'received')>Alındı</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1" for="supplier_id">Tedarikçi</label>
                    <input class="form-control form-control-sm" type="number" id="supplier_id" name="supplier_id" value="{{ $filters['supplier_id'] ?? '' }}" min="1">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1" for="search">Ara</label>
                    <input class="form-control form-control-sm" type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="PO no / not">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1" for="from">Başlangıç</label>
                    <input class="form-control form-control-sm" type="date" id="from" name="from" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1" for="to">Bitiş</label>
                    <input class="form-control form-control-sm" type="date" id="to" name="to" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary btn-sm" type="submit">Filtrele</button>
                </div>
            </form>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-semibold small mb-1">Açık PO</p>
                        <p class="display-6 mb-0">{{ number_format($state['kpis']['open_pos'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-semibold small mb-1">Teslim Bekleyen</p>
                        <p class="display-6 mb-0">{{ number_format($state['kpis']['awaiting_receipt'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-semibold small mb-1">Kabul Kuyruğu</p>
                        <p class="display-6 mb-0">{{ number_format($state['kpis']['pending_grn'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-semibold small mb-1">Yaklaşan Ödemeler</p>
                        <p class="display-6 mb-0">{{ number_format($state['kpis']['ap_due'] ?? 0) }}</p>
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
                <div class="col-lg-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">{{ $stage['label'] ?? 'Aksiyon' }}</span>
                            <span class="badge text-bg-primary">{{ number_format($stage['count'] ?? 0) }}</span>
                        </div>
                        <div class="card-body">
                            @if(!empty($stage['rows']))
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                        <tr>
                                            <th scope="col">Referans</th>
                                            <th scope="col">Durum</th>
                                            <th scope="col" class="text-end">Tutar</th>
                                            <th scope="col" class="text-end">Aksiyon</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($stage['rows'] as $row)
                                            @php($field = $actionFields[$stage['action']] ?? null)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $row['reference'] ?? ('PO-' . ($row['id'] ?? '')) }}</div>
                                                    <small class="text-muted">{{ $row['supplier_id'] ? 'Tedarikçi #' . $row['supplier_id'] : '' }}</small>
                                                </td>
                                                <td>{{ $row['status'] ?? '—' }}</td>
                                                <td class="text-end">{{ isset($row['total']) ? number_format($row['total'], 2) : (isset($row['balance_due']) ? number_format($row['balance_due'], 2) : '—') }}</td>
                                                <td class="text-end">
                                                    <form method="post" class="d-inline" action="{{ route('consoles.p2p.execute', $stage['action']) }}">
                                                        @csrf
                                                        @if($field && isset($row['id']))
                                                            <input type="hidden" name="{{ $field }}" value="{{ $row['id'] }}">
                                                        @endif
                                                        <button class="btn btn-sm btn-outline-primary" type="submit">Çalıştır</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0">Bu adımda bekleyen kayıt bulunmuyor.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
