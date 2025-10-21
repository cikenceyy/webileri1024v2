@extends('layouts.admin')

@section('title', 'Satınalma Siparişi Detayı')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Satınalma Siparişi {{ $purchaseOrder->po_number ?? ('#' . $purchaseOrder->id) }}</h1>
            <p class="text-muted mb-0">Tedarikçi ID: {{ $purchaseOrder->supplier_id }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.procurement.pos.index') }}" class="btn btn-outline-secondary">Listeye Dön</a>
            <a href="{{ route('admin.procurement.grns.create', ['purchase_order_id' => $purchaseOrder->id]) }}" class="btn btn-outline-primary">Mal Kabulü</a>
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
                    <span class="badge rounded-pill text-bg-{{ $purchaseOrder->status === 'closed' ? 'success' : ($purchaseOrder->status === 'approved' ? 'primary' : 'secondary') }}">
                        {{ strtoupper($purchaseOrder->status) }}
                    </span>
                </dd>
                <dt class="col-sm-3">Toplam</dt>
                <dd class="col-sm-9">{{ number_format((float) $purchaseOrder->total, 2, ',', '.') }} {{ $purchaseOrder->currency }}</dd>
                <dt class="col-sm-3">Oluşturulma</dt>
                <dd class="col-sm-9">{{ $purchaseOrder->created_at?->format('d.m.Y H:i') }}</dd>
                <dt class="col-sm-3">Onaylanma</dt>
                <dd class="col-sm-9">{{ $purchaseOrder->approved_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                <dt class="col-sm-3">Kapanış</dt>
                <dd class="col-sm-9">{{ $purchaseOrder->closed_at?->format('d.m.Y H:i') ?? '—' }}</dd>
            </dl>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Satır Detayları</h2>
            @if($purchaseOrder->status === 'draft')
                <form action="{{ route('admin.procurement.pos.update', $purchaseOrder) }}" method="post" class="d-inline">
                    @csrf
                    @method('patch')
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="btn btn-sm btn-success">Siparişi Onayla</button>
                </form>
            @elseif($purchaseOrder->status === 'approved')
                <form action="{{ route('admin.procurement.pos.update', $purchaseOrder) }}" method="post" class="d-inline">
                    @csrf
                    @method('patch')
                    <input type="hidden" name="status" value="closed">
                    <button type="submit" class="btn btn-sm btn-outline-success">Siparişi Kapat</button>
                </form>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Ürün</th>
                        <th scope="col">Açıklama</th>
                        <th scope="col" class="text-end">Sipariş Miktarı</th>
                        <th scope="col" class="text-end">Toplam</th>
                        <th scope="col" class="text-end">Teslim Alınan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->lines as $line)
                        @php
                            $received = $line->grnLines->sum('qty_received');
                        @endphp
                        <tr>
                            <td>{{ $line->product_id ?? '—' }}</td>
                            <td>{{ $line->description }}</td>
                            <td class="text-end">{{ number_format((float) $line->qty_ordered, 3, ',', '.') }} {{ $line->unit }}</td>
                            <td class="text-end">{{ number_format((float) $line->line_total, 2, ',', '.') }} {{ $purchaseOrder->currency }}</td>
                            <td class="text-end">{{ number_format((float) $received, 3, ',', '.') }} {{ $line->unit }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="h5 mb-0">Mal Kabul Kayıtları</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">GRN</th>
                        <th scope="col">Durum</th>
                        <th scope="col">Tarih</th>
                        <th scope="col" class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrder->goodsReceipts as $grn)
                        <tr>
                            <td>#{{ $grn->id }}</td>
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
                            <td colspan="4" class="text-center py-5 text-muted">Henüz mal kabul kaydı bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
