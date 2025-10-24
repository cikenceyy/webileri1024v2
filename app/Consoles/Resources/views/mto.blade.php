@extends('layouts.admin')

@php($module = 'Consoles')
@php($page = 'mto')
@php($hasFilters = ! empty(array_filter($filters ?? [])))

@section('content')
    <div class="container-fluid py-4 console-page">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 mb-2">Make-to-Order Konsolu</h1>
                <p class="text-muted mb-0">İş emirlerini, malzeme akışlarını ve üretim kapanışlarını tek panelden yönetin.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <span class="text-uppercase small text-muted fw-semibold">Filtreler</span>
                @if($hasFilters)
                    <a class="btn btn-link btn-sm text-decoration-none" href="{{ route('consoles.mto') }}">Temizle</a>
                @endif
            </div>
            <div class="card-body">
                <form method="get" class="row g-3 align-items-end" action="{{ route('consoles.mto') }}">
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label" for="status">Durum</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tümü</option>
                            <option value="draft" @selected(($filters['status'] ?? null) === 'draft')>Taslak</option>
                            <option value="released" @selected(($filters['status'] ?? null) === 'released')>Serbest</option>
                            <option value="in_progress" @selected(($filters['status'] ?? null) === 'in_progress')>Üretimde</option>
                            <option value="completed" @selected(($filters['status'] ?? null) === 'completed')>Tamamlandı</option>
                            <option value="closed" @selected(($filters['status'] ?? null) === 'closed')>Kapandı</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label" for="product_id">Ürün</label>
                        <input class="form-control" type="number" id="product_id" name="product_id" value="{{ $filters['product_id'] ?? '' }}" min="1" placeholder="Ürün ID">
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
                        <span class="text-uppercase small text-muted fw-semibold">Taslak</span>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['kpis']['draft'] ?? 0) }}</span>
                            <span class="text-muted small">iş emri</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted fw-semibold">Üretimde</span>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['kpis']['in_progress'] ?? 0) }}</span>
                            <span class="text-muted small">aktif</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted fw-semibold">Tamamlandı</span>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['kpis']['completed'] ?? 0) }}</span>
                            <span class="text-muted small">iş emri</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted fw-semibold">Kapatıldı</span>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['kpis']['closed'] ?? 0) }}</span>
                            <span class="text-muted small">iş emri</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php($actionFields = [
            'wo.release' => 'work_order_id',
            'wo.issue.materials' => 'work_order_id',
            'wo.finish' => 'work_order_id',
            'wo.close' => 'work_order_id',
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
                                    <p class="text-muted small mb-0">Bu adımda bekleyen en güncel iş emirleri listelenir.</p>
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
                                                        <div class="fw-semibold">{{ $row['doc_no'] ?? ('WO-' . ($row['id'] ?? '')) }}</div>
                                                        @if(!empty($row['due_date']))
                                                            <div class="text-muted small">Termin: {{ $row['due_date'] }}</div>
                                                        @endif
                                                    </div>
                                                    <span class="badge text-bg-light text-capitalize">{{ str($row['status'] ?? '')->replace('_', ' ')->headline() ?: '—' }}</span>
                                                </div>
                                                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between align-items-sm-center">
                                                    <div class="text-muted small">Miktar: {{ isset($row['qty']) ? number_format($row['qty'], 2) : '—' }}</div>
                                                    <form method="post" action="{{ route('consoles.mto.execute', $stage['action']) }}" class="text-sm-end">
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
                                    <p class="mb-0 small">Bu adım için aksiyon gerektiren iş emri bulunmuyor.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
