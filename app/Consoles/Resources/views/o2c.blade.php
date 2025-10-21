@extends('layouts.admin')

@php($module = 'Consoles')
@php($page = 'o2c')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="h4 mb-1">Order-to-Cash Konsolu</h1>
                <p class="text-muted mb-0">Siparişten tahsilata kadar tüm adımları tek ekrandan yönetin.</p>
            </div>
            <form method="get" class="row g-2 align-items-end" action="{{ route('consoles.o2c') }}">
                <div class="col-auto">
                    <label class="form-label mb-1" for="status">Durum</label>
                    <select class="form-select form-select-sm" id="status" name="status">
                        <option value="">Tümü</option>
                        <option value="draft" @selected(($filters['status'] ?? null) === 'draft')>Taslak</option>
                        <option value="confirmed" @selected(($filters['status'] ?? null) === 'confirmed')>Onaylandı</option>
                        <option value="ready_to_invoice" @selected(($filters['status'] ?? null) === 'ready_to_invoice')>Faturalandırılacak</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1" for="customer_id">Müşteri</label>
                    <input class="form-control form-control-sm" type="number" id="customer_id" name="customer_id" value="{{ $filters['customer_id'] ?? '' }}" placeholder="ID" min="1">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1" for="search">Ara</label>
                    <input class="form-control form-control-sm" type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Sipariş no / müşteri">
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
                        <p class="text-muted text-uppercase fw-semibold small mb-1">Açık Sipariş</p>
                        <p class="display-6 mb-0">{{ number_format($state['kpis']['open_orders'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-semibold small mb-1">Sevk Bekleyen</p>
                        <p class="display-6 mb-0">{{ number_format($state['kpis']['awaiting_fulfilment'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-semibold small mb-1">Yaklaşan Tahsilat</p>
                        <p class="display-6 mb-0">{{ number_format($state['kpis']['invoices_due'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-semibold small mb-1">Sevkiyat Süreci</p>
                        <p class="display-6 mb-0">{{ number_format($state['kpis']['shipments_in_progress'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        @php($actionFields = [
            'so.confirm' => 'order_id',
            'inv.allocate' => 'order_id',
            'ship.dispatch' => 'shipment_id',
            'ar.invoice.post' => 'order_id',
            'ar.payment.register' => 'invoice_id',
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
                                                    <div class="fw-semibold">{{ $row['order_no'] ?? $row['shipment_no'] ?? $row['invoice_no'] ?? ('#' . ($row['id'] ?? '')) }}</div>
                                                    <small class="text-muted">{{ $row['customer'] ?? ($row['ship_date'] ?? $row['due_date'] ?? '') }}</small>
                                                </td>
                                                <td>{{ $row['status'] ?? '—' }}</td>
                                                <td class="text-end">
                                                    {{ isset($row['total']) ? number_format($row['total'], 2) : (isset($row['balance_due']) ? number_format($row['balance_due'], 2) : '—') }}
                                                </td>
                                                <td class="text-end">
                                                    <form method="post" class="d-inline" action="{{ route('consoles.o2c.execute', $stage['action']) }}">
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
