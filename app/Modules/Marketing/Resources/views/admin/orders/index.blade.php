@extends('layouts.admin')

@section('title', 'Satış Siparişleri')
@section('module', 'Marketing')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Satış Siparişleri</h1>
        <a href="{{ route('admin.marketing.orders.create') }}" class="btn btn-primary">Yeni Sipariş</a>
    </div>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Sipariş no veya müşteri">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Durum (Tümü)</option>
                @foreach (['draft' => 'Taslak', 'confirmed' => 'Onaylı', 'fulfilled' => 'Tamamlandı', 'cancelled' => 'İptal'] as $key => $label)
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
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Belge No</th>
                        <th>Müşteri</th>
                        <th>Durum</th>
                        <th>Vade</th>
                        <th>Oluşturulma</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->doc_no }}</td>
                            <td>{{ optional($order->customer)->name }}</td>
                            <td>
                                @php($statusMap = ['draft' => 'warning', 'confirmed' => 'primary', 'fulfilled' => 'success', 'cancelled' => 'secondary'])
                                <span class="badge bg-{{ $statusMap[$order->status] ?? 'light' }} text-uppercase">{{ $order->status }}</span>
                            </td>
                            <td>{{ $order->due_date?->format('d.m.Y') ?? '—' }}</td>
                            <td>{{ $order->created_at?->format('d.m.Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.marketing.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">Detay</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Henüz sipariş kaydı bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $orders->links() }}
        </div>
    </div>
@endsection
