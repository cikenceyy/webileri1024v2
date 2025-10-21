@extends('layouts.admin')

@php($module = 'Consoles')
@php($page = 'mto')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="h4 mb-1">Make-to-Order Konsolu</h1>
                <p class="text-muted mb-0">İş emirlerini, malzeme hareketlerini ve tamamlanan üretimleri yönetin.</p>
            </div>
            <form method="get" class="row g-2 align-items-end" action="{{ route('consoles.mto') }}">
                <div class="col-auto">
                    <label class="form-label mb-1" for="status">Durum</label>
                    <select class="form-select form-select-sm" id="status" name="status">
                        <option value="">Tümü</option>
                        <option value="draft" @selected(($filters['status'] ?? null) === 'draft')>Taslak</option>
                        <option value="released" @selected(($filters['status'] ?? null) === 'released')>Serbest</option>
                        <option value="in_progress" @selected(($filters['status'] ?? null) === 'in_progress')>Üretimde</option>
                        <option value="done" @selected(($filters['status'] ?? null) === 'done')>Tamamlandı</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1" for="product_id">Ürün</label>
                    <input class="form-control form-control-sm" type="number" id="product_id" name="product_id" value="{{ $filters['product_id'] ?? '' }}" min="1">
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
                        <p class="text-muted text-uppercase fw-semibold small mb-1">Taslak</p>
                        <p class="display-6 mb-0">{{ number_format($state['kpis']['draft'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-semibold small mb-1">Üretimde</p>
                        <p class="display-6 mb-0">{{ number_format($state['kpis']['in_progress'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-semibold small mb-1">Kontrol Bekleyen</p>
                        <p class="display-6 mb-0">{{ number_format($state['kpis']['awaiting_qc'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-semibold small mb-1">Tamamlandı</p>
                        <p class="display-6 mb-0">{{ number_format($state['kpis']['completed'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        @php($actionFields = [
            'wo.release' => 'work_order_id',
            'wo.issue.materials' => 'work_order_id',
            'wo.finish' => 'work_order_id',
            'inv.receive.finished' => 'work_order_id',
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
                                            <th scope="col">İş Emri</th>
                                            <th scope="col">Durum</th>
                                            <th scope="col">Miktar</th>
                                            <th scope="col" class="text-end">Aksiyon</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($stage['rows'] as $row)
                                            @php($field = $actionFields[$stage['action']] ?? null)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $row['work_order_no'] ?? ('WO-' . ($row['id'] ?? '')) }}</div>
                                                    <small class="text-muted">{{ $row['due_date'] ? 'Termin: ' . $row['due_date'] : '' }}</small>
                                                </td>
                                                <td>{{ $row['status'] ?? '—' }}</td>
                                                <td>{{ isset($row['qty']) ? number_format($row['qty'], 2) : '—' }}</td>
                                                <td class="text-end">
                                                    <form method="post" class="d-inline" action="{{ route('consoles.mto.execute', $stage['action']) }}">
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
