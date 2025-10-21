@extends('layouts.admin')

@section('title', 'Mal Kabul Detayı')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Mal Kabul #{{ $grn->id }}</h1>
            <p class="text-muted mb-0">Satınalma Siparişi #{{ $grn->purchase_order_id }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.procurement.grns.index') }}" class="btn btn-outline-secondary">Listeye Dön</a>
            <a href="{{ route('admin.procurement.pos.show', $grn->purchaseOrder) }}" class="btn btn-outline-primary">PO Detayı</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Durum</dt>
                <dd class="col-sm-9">
                    <span class="badge rounded-pill text-bg-{{ $grn->status === 'received' ? 'success' : 'primary' }}">{{ strtoupper($grn->status) }}</span>
                </dd>
                <dt class="col-sm-3">Kabul Tarihi</dt>
                <dd class="col-sm-9">{{ $grn->received_at?->format('d.m.Y H:i') ?? $grn->created_at?->format('d.m.Y H:i') }}</dd>
                <dt class="col-sm-3">Oluşturulma</dt>
                <dd class="col-sm-9">{{ $grn->created_at?->format('d.m.Y H:i') }}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="h5 mb-0">Satır Detayları</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">PO Satırı</th>
                        <th scope="col">Açıklama</th>
                        <th scope="col" class="text-end">Teslim Alınan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grn->lines as $line)
                        <tr>
                            <td>#{{ $line->po_line_id }}</td>
                            <td>{{ $line->poLine?->description ?? '—' }}</td>
                            <td class="text-end">{{ number_format((float) $line->qty_received, 3, ',', '.') }} {{ $line->poLine?->unit }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
