@extends('layouts.admin')

@section('title', 'Mal Kabul Detayı')
@section('module', 'Logistics')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">GRN #{{ $receipt->doc_no }}</h1>
            <span class="badge bg-light text-dark text-capitalize">{{ $receipt->status }}</span>
        </div>
        <div class="d-flex gap-2">
            @if (! in_array($receipt->status, ['received', 'reconciled', 'closed', 'cancelled']))
                <a href="{{ route('admin.logistics.receipts.edit', $receipt) }}" class="btn btn-outline-primary">Düzenle</a>
            @endif
            <a href="{{ route('admin.logistics.receipts.print', $receipt) }}" class="btn btn-outline-secondary" target="_blank">Yazdır</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Tedarikçi</h6>
                    <p class="mb-0">{{ $receipt->vendor_id ? ('#' . $receipt->vendor_id) : '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Depo</h6>
                    <p class="mb-0">{{ $receipt->warehouse?->name ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Tarih</h6>
                    <p class="mb-0">{{ optional($receipt->received_at)->format('d.m.Y H:i') ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th>Beklenen</th>
                        <th>Alınan</th>
                        <th>Varyans</th>
                        <th>Not</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($receipt->lines as $line)
                        <tr>
                            <td>{{ $line->product?->name ?? ('#' . $line->product_id) }}</td>
                            <td>{{ number_format($line->qty_expected ?? 0, 3) }}</td>
                            <td>{{ number_format($line->qty_received ?? 0, 3) }}</td>
                            <td>{{ number_format(($line->qty_received ?? 0) - ($line->qty_expected ?? 0), 3) }}</td>
                            <td>{{ $line->variance_reason ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if (in_array($receipt->status, ['draft', 'received']))
        @include('logistics::admin.receipts.partials.receive-form', ['receipt' => $receipt, 'warehouses' => $warehouses, 'bins' => $bins])
    @endif

    @if ($receipt->status === 'received')
        @include('logistics::admin.receipts.partials.reconcile-form', ['receipt' => $receipt])
    @endif

    <div class="d-flex gap-2 mt-4">
        @if (in_array($receipt->status, ['received', 'reconciled']))
            <form method="post" action="{{ route('admin.logistics.receipts.close', $receipt) }}">
                @csrf
                <button class="btn btn-outline-success">Kapat</button>
            </form>
        @endif
        @if (! in_array($receipt->status, ['closed', 'cancelled']))
            <form method="post" action="{{ route('admin.logistics.receipts.cancel', $receipt) }}" onsubmit="return confirm('Kayıt iptal edilsin mi?');">
                @csrf
                <button class="btn btn-outline-danger">İptal Et</button>
            </form>
        @endif
    </div>
@endsection
