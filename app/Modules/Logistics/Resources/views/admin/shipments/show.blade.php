@php use Illuminate\Support\Str; @endphp
@extends('layouts.admin')

@section('title', 'Sevkiyat Detayı')
@section('module', 'Logistics')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Sevkiyat #{{ $shipment->doc_no }}</h1>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-light text-dark text-capitalize">{{ $shipment->status }}</span>
                @if ($shipment->shipped_at)
                    <span class="text-muted small">{{ $shipment->shipped_at->format('d.m.Y H:i') }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.logistics.shipments.print', $shipment) }}" class="btn btn-outline-secondary" target="_blank">Yazdır</a>
            @if (! in_array($shipment->status, ['shipped', 'closed', 'cancelled']))
                <a href="{{ route('admin.logistics.shipments.edit', $shipment) }}" class="btn btn-outline-primary">Düzenle</a>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Müşteri</h6>
                    <p class="mb-0">{{ $shipment->customer?->name ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Depo / Paket</h6>
                    <p class="mb-0">{{ $shipment->warehouse?->name ?? '—' }} | Paket: {{ $shipment->packages_count ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Ağırlık</h6>
                    <p class="mb-0">Brüt: {{ $shipment->gross_weight ?? '—' }} / Net: {{ $shipment->net_weight ?? '—' }}</p>
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
                        <th>İstenen</th>
                        <th>Toplanan</th>
                        <th>Paketlenen</th>
                        <th>Sevk Edilen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($shipment->lines as $line)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $line->product?->name ?? ('#' . $line->product_id) }}</div>
                                <div class="text-muted small">Depo: {{ $line->warehouse?->name ?? '—' }} / Raf: {{ $line->bin?->code ?? '—' }}</div>
                            </td>
                            <td>{{ number_format($line->qty, 3) }}</td>
                            <td>{{ number_format($line->picked_qty, 3) }}</td>
                            <td>{{ number_format($line->packed_qty, 3) }}</td>
                            <td>{{ number_format($line->shipped_qty, 3) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if (in_array($shipment->status, ['draft', 'picking']))
        <form method="post" action="{{ route('admin.logistics.shipments.startPicking', $shipment) }}" class="mb-3">
            @csrf
            <button class="btn btn-sm btn-outline-secondary">Toplamayı Başlat</button>
        </form>
    @endif

    @if (in_array($shipment->status, ['draft', 'picking']))
        @include('logistics::admin.shipments.partials.pick-form', ['shipment' => $shipment, 'warehouses' => $warehouses, 'bins' => $bins])
    @endif

    @if (in_array($shipment->status, ['picking', 'packed']))
        @include('logistics::admin.shipments.partials.pack-form', ['shipment' => $shipment])
    @endif

    <div class="d-flex gap-2 mt-4">
        @if ($shipment->status === 'packed')
            <form method="post" action="{{ route('admin.logistics.shipments.ship', $shipment) }}">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ (string) Str::uuid() }}">
                <button class="btn btn-success">Sevk Et</button>
            </form>
        @endif
        @if ($shipment->status === 'shipped')
            <form method="post" action="{{ route('admin.logistics.shipments.close', $shipment) }}">
                @csrf
                <button class="btn btn-outline-success">Kapat</button>
            </form>
        @endif
        @if (! in_array($shipment->status, ['closed', 'cancelled']))
            <form method="post" action="{{ route('admin.logistics.shipments.cancel', $shipment) }}" onsubmit="return confirm('Sevkiyat iptal edilsin mi?');">
                @csrf
                <button class="btn btn-outline-danger">İptal Et</button>
            </form>
        @endif
    </div>
@endsection
