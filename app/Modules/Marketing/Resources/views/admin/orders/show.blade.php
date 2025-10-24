@extends('layouts.admin')

@section('title', $order->doc_no)
@section('module', 'Marketing')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.marketing.orders.index') }}" class="btn btn-link p-0 me-3">&larr; Listeye dön</a>
            <h1 class="h3 mb-0">{{ $order->doc_no }}</h1>
            <p class="text-muted mb-0">Müşteri: {{ optional($order->customer)->name ?? '—' }}</p>
        </div>
        <div class="d-flex gap-2">
            @can('update', $order)
                <a href="{{ route('admin.marketing.orders.edit', $order) }}" class="btn btn-outline-primary">Düzenle</a>
            @endcan
            @can('confirm', $order)
                <form method="post" action="{{ route('admin.marketing.orders.confirm', $order) }}" onsubmit="return confirm('Siparişi onaylamak istediğinize emin misiniz?');">
                    @csrf
                    <button class="btn btn-success">Onayla</button>
                </form>
            @endcan
            @can('cancel', $order)
                <form method="post" action="{{ route('admin.marketing.orders.cancel', $order) }}" onsubmit="return confirm('Siparişi iptal etmek istediğinize emin misiniz?');">
                    @csrf
                    <button class="btn btn-outline-danger">İptal Et</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">Özet</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Durum</dt>
                        <dd class="col-sm-7 text-uppercase">{{ $order->status }}</dd>
                        <dt class="col-sm-5">Para Birimi</dt>
                        <dd class="col-sm-7">{{ $order->currency }}</dd>
                        <dt class="col-sm-5">Vade Tarihi</dt>
                        <dd class="col-sm-7">{{ $order->due_date?->format('d.m.Y') ?? '—' }}</dd>
                        <dt class="col-sm-5">Vergi Dahil</dt>
                        <dd class="col-sm-7">{{ $order->tax_inclusive ? 'Evet' : 'Hayır' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">Notlar</div>
                <div class="card-body">
                    <p class="mb-0 text-muted">{{ $order->notes ?: 'Not bulunmuyor.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Satır Kalemleri</div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th>Miktar</th>
                        <th>Birim Fiyat</th>
                        <th>İskonto %</th>
                        <th>Vergi %</th>
                        <th>Stok</th>
                        <th>Satır Tutarı</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->lines as $line)
                        @php($signal = $signals[$line->id] ?? null)
                        <tr>
                            <td>{{ optional($line->product)->name ?? '—' }}</td>
                            <td>{{ number_format($line->qty, 3, ',', '.') }} {{ $line->uom }}</td>
                            <td>{{ number_format($line->unit_price, 2, ',', '.') }}</td>
                            <td>{{ number_format($line->discount_pct ?? 0, 2) }}</td>
                            <td>{{ $line->tax_rate !== null ? number_format($line->tax_rate, 2) : '—' }}</td>
                            <td>
                                @if ($signal)
                                    @php($colorMap = ['in' => 'success', 'low' => 'warning', 'out' => 'danger'])
                                    <span class="badge bg-{{ $colorMap[$signal['status']] ?? 'secondary' }}">{{ strtoupper($signal['status']) }} • {{ $signal['formatted'] }}</span>
                                @else
                                    <span class="badge bg-secondary">—</span>
                                @endif
                            </td>
                            <td>{{ number_format($line->line_total, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
