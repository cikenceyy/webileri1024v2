@extends('layouts.admin')

@section('title', $workOrder->doc_no)
@section('module', 'Production')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $workOrder->doc_no }}</h1>
            <p class="text-muted mb-0">{{ $workOrder->product?->name }} • {{ number_format($workOrder->target_qty, 3) }} {{ $workOrder->uom }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($workOrder->status === 'draft')
                @can('release', $workOrder)
                    <form action="{{ route('admin.production.workorders.release', $workOrder) }}" method="post">@csrf<button class="btn btn-outline-primary" type="submit">Serbest Bırak</button></form>
                @endcan
                @can('start', $workOrder)
                    <form action="{{ route('admin.production.workorders.start', $workOrder) }}" method="post">@csrf<button class="btn btn-outline-secondary" type="submit">Üretime Al</button></form>
                @endcan
            @elseif($workOrder->status === 'released')
                @can('start', $workOrder)
                    <form action="{{ route('admin.production.workorders.start', $workOrder) }}" method="post">@csrf<button class="btn btn-outline-secondary" type="submit">Üretime Al</button></form>
                @endcan
            @endif
            @if(in_array($workOrder->status, ['in_progress', 'completed']))
                @can('close', $workOrder)
                    <form action="{{ route('admin.production.workorders.close', $workOrder) }}" method="post">@csrf<button class="btn btn-outline-success" type="submit">Kapat</button></form>
                @endcan
            @endif
            @if(!in_array($workOrder->status, ['closed', 'completed']))
                @can('cancel', $workOrder)
                    <form action="{{ route('admin.production.workorders.cancel', $workOrder) }}" method="post">@csrf<button class="btn btn-outline-danger" type="submit">İptal</button></form>
                @endcan
            @endif
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted">Durum</h6>
                    <p class="fs-5 mb-0">{{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted">Termin</h6>
                    <p class="fs-5 mb-0">{{ optional($workOrder->due_date)->format('d.m.Y') ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted">Başlangıç / Tamamlanma</h6>
                    <p class="mb-0">{{ optional($workOrder->started_at)->format('d.m.Y H:i') ?? '—' }} → {{ optional($workOrder->completed_at)->format('d.m.Y H:i') ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">BOM İhtiyaçları ({{ $workOrder->bom?->code }})</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>Bileşen</th>
                    <th>Gereken</th>
                    <th>Fire %</th>
                </tr>
                </thead>
                <tbody>
                @foreach($requirements as $requirement)
                    <tr>
                        <td>{{ $requirement['item']->component?->name ?? 'Malzeme' }}</td>
                        <td>{{ number_format($requirement['required_qty'], 3) }}</td>
                        <td>{{ number_format($requirement['item']->wastage_pct, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('production::admin.workorders.partials.issue-form')
    @include('production::admin.workorders.partials.complete-form')

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Malzeme Çıkışları</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Malzeme</th>
                            <th>Miktar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($workOrder->issues as $issue)
                            <tr>
                                <td>{{ optional($issue->posted_at)->format('d.m.Y H:i') }}</td>
                                <td>{{ $issue->component?->name ?? '—' }}</td>
                                <td>-{{ number_format($issue->qty, 3) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3">Kayıt bulunmuyor.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Üretim Girişleri</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Miktar</th>
                            <th>Depo</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($workOrder->receipts as $receipt)
                            <tr>
                                <td>{{ optional($receipt->posted_at)->format('d.m.Y H:i') }}</td>
                                <td>{{ number_format($receipt->qty, 3) }}</td>
                                <td>{{ $receipt->warehouse?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3">Kayıt bulunmuyor.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
