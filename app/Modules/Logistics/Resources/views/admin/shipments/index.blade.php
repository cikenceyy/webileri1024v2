@extends('layouts.admin')

@section('title', 'Sevkiyatlar')
@section('module', 'Logistics')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Sevkiyatlar</h1>
        <a href="{{ route('admin.logistics.shipments.create') }}" class="btn btn-primary">Yeni Sevkiyat</a>
    </div>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">Durum (Tümü)</option>
                @foreach (['draft' => 'Taslak', 'picking' => 'Toplanıyor', 'packed' => 'Paketlendi', 'shipped' => 'Sevk Edildi', 'closed' => 'Kapandı', 'cancelled' => 'İptal'] as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">Filtrele</button>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Belge No</th>
                        <th>Müşteri</th>
                        <th>Durum</th>
                        <th>Paket</th>
                        <th>Sevk Tarihi</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shipments as $shipment)
                        <tr>
                            <td class="fw-semibold">{{ $shipment->doc_no }}</td>
                            <td>{{ $shipment->customer?->name ?? '—' }}</td>
                            <td><span class="badge bg-light text-dark text-capitalize">{{ $shipment->status }}</span></td>
                            <td>{{ $shipment->packages_count ?? '—' }}</td>
                            <td>{{ optional($shipment->shipped_at)->format('d.m.Y H:i') ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.logistics.shipments.show', $shipment) }}" class="btn btn-sm btn-outline-primary">Görüntüle</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Henüz sevkiyat bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $shipments->links() }}
    </div>
@endsection
