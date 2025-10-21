@extends('layouts.admin')

@section('title', 'İş Emri Detayı')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $workOrder->work_order_no }}</h1>
            <p class="text-muted mb-0">Siparişten türeyen üretim iş emri.</p>
        </div>
        <a href="{{ route('admin.production.work-orders.index') }}" class="btn btn-outline-secondary">Tüm İş Emirleri</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Genel Bilgiler</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Sipariş</dt>
                        <dd class="col-sm-8">{{ $workOrder->order?->order_no ?? '—' }}</dd>

                        <dt class="col-sm-4 text-muted">Ürün</dt>
                        <dd class="col-sm-8">
                            @if($workOrder->product)
                                <div class="fw-semibold">{{ $workOrder->product->name }}</div>
                                <div class="text-muted small">SKU: {{ $workOrder->product->sku }}</div>
                            @else
                                —
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">Miktar</dt>
                        <dd class="col-sm-8">{{ number_format((float) $workOrder->qty, 3, ',', '.') }} {{ $workOrder->unit }}</dd>

                        <dt class="col-sm-4 text-muted">Durum</dt>
                        <dd class="col-sm-8">
                            <span class="badge rounded-pill text-bg-{{ $workOrder->status === 'done' ? 'success' : ($workOrder->status === 'in_progress' ? 'primary' : 'secondary') }}">{{ strtoupper($workOrder->status) }}</span>
                            @if($workOrder->closed_at)
                                <span class="text-muted small d-block mt-1">Kapatma: {{ $workOrder->closed_at->format('d.m.Y H:i') }}</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">Termin</dt>
                        <dd class="col-sm-8">{{ $workOrder->due_date?->format('d.m.Y') ?? '—' }}</dd>

                        <dt class="col-sm-4 text-muted">Notlar</dt>
                        <dd class="col-sm-8">{{ $workOrder->notes ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">Durum Güncelle</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.production.work-orders.update', $workOrder) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="status" class="form-label">Durum</label>
                            <select name="status" id="status" class="form-select">
                                @foreach(['draft' => 'Taslak', 'planned' => 'Planlandı', 'in_progress' => 'Üretimde', 'done' => 'Tamamlandı', 'cancelled' => 'İptal'] as $value => $label)
                                    <option value="{{ $value }}" @selected($workOrder->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="planned_start_date" class="form-label">Başlangıç</label>
                            <input type="date" class="form-control" id="planned_start_date" name="planned_start_date" value="{{ $workOrder->planned_start_date?->format('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label for="due_date" class="form-label">Termin</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="{{ $workOrder->due_date?->format('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notlar</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $workOrder->notes) }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Kaydet</button>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header">İş Emrini Kapat</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.production.work-orders.close', $workOrder) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success w-100" @disabled($workOrder->status === 'done')>Tamamlandı Olarak İşaretle</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
