@extends('layouts.admin')

@section('title', 'Satınalma Siparişleri')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Satınalma Siparişleri</h1>
            <p class="text-muted mb-0">Tedarik siparişlerinizi ve durumlarını izleyin.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.procurement.pos.index') }}" class="btn btn-outline-secondary">Yenile</a>
            <a href="{{ route('admin.procurement.pos.create') }}" class="btn btn-primary">Yeni Sipariş</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Sipariş</th>
                        <th scope="col">Tedarikçi</th>
                        <th scope="col">Durum</th>
                        <th scope="col" class="text-end">Toplam</th>
                        <th scope="col">Oluşturulma</th>
                        <th scope="col" class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $purchaseOrder)
                        <tr>
                            <td>{{ $purchaseOrder->po_number ?? ('#' . $purchaseOrder->id) }}</td>
                            <td>{{ $purchaseOrder->supplier_id }}</td>
                            <td>
                                <span class="badge rounded-pill text-bg-{{ $purchaseOrder->status === 'closed' ? 'success' : ($purchaseOrder->status === 'approved' ? 'primary' : 'secondary') }}">
                                    {{ strtoupper($purchaseOrder->status) }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format((float) $purchaseOrder->total, 2, ',', '.') }} {{ $purchaseOrder->currency }}</td>
                            <td>{{ $purchaseOrder->created_at?->format('d.m.Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.procurement.pos.show', $purchaseOrder) }}" class="btn btn-sm btn-outline-primary">Görüntüle</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Henüz satınalma siparişi bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $purchaseOrders->links() }}
        </div>
    </div>
@endsection
