@extends('layouts.admin')

@section('title', 'Mal Kabul Kayıtları')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Mal Kabul Kayıtları</h1>
            <p class="text-muted mb-0">Satınalma siparişleriniz için teslimat ilerlemesini takip edin.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.procurement.grns.index') }}" class="btn btn-outline-secondary">Yenile</a>
            <a href="{{ route('admin.procurement.grns.create') }}" class="btn btn-primary">Yeni Mal Kabulü</a>
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
                        <th scope="col">GRN</th>
                        <th scope="col">Sipariş</th>
                        <th scope="col">Durum</th>
                        <th scope="col">Tarih</th>
                        <th scope="col" class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($goodsReceipts as $grn)
                        <tr>
                            <td>#{{ $grn->id }}</td>
                            <td>
                                <a href="{{ route('admin.procurement.pos.show', $grn->purchaseOrder) }}" class="text-decoration-none">
                                    #{{ $grn->purchase_order_id }}
                                </a>
                            </td>
                            <td>
                                <span class="badge rounded-pill text-bg-{{ $grn->status === 'received' ? 'success' : 'primary' }}">{{ strtoupper($grn->status) }}</span>
                            </td>
                            <td>{{ $grn->received_at?->format('d.m.Y H:i') ?? $grn->created_at?->format('d.m.Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.procurement.grns.show', $grn) }}" class="btn btn-sm btn-outline-primary">Görüntüle</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Henüz mal kabul kaydı bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $goodsReceipts->links() }}
        </div>
    </div>
@endsection
