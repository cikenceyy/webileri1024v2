@extends('layouts.admin')

@section('title', 'İade Talebi Detayı')
@section('module', 'Marketing')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.marketing.returns.index') }}" class="btn btn-link p-0 me-3">&larr; Listeye dön</a>
            <h1 class="h3 mb-0">{{ optional($return->customer)->name ?? 'İade Talebi' }}</h1>
            <p class="text-muted mb-0">Durum: {{ strtoupper($return->status) }}</p>
        </div>
        <div class="d-flex gap-2">
            @can('approve', $return)
                <form method="post" action="{{ route('admin.marketing.returns.approve', $return) }}" onsubmit="return confirm('Talebi onaylamak istediğinize emin misiniz?');">
                    @csrf
                    <button class="btn btn-success">Onayla</button>
                </form>
            @endcan
            @can('close', $return)
                <form method="post" action="{{ route('admin.marketing.returns.close', $return) }}" onsubmit="return confirm('Talebi kapatmak istediğinize emin misiniz?');">
                    @csrf
                    <button class="btn btn-outline-danger">Kapat</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">Özet</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Müşteri</dt>
                        <dd class="col-sm-8">{{ optional($return->customer)->name ?? '—' }}</dd>
                        <dt class="col-sm-4">Sipariş Ref.</dt>
                        <dd class="col-sm-8">{{ $return->related_order_id ?? '—' }}</dd>
                        <dt class="col-sm-4">Oluşturulma</dt>
                        <dd class="col-sm-8">{{ $return->created_at?->format('d.m.Y H:i') }}</dd>
                        <dt class="col-sm-4">Genel Neden</dt>
                        <dd class="col-sm-8">{{ $return->reason ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">Notlar</div>
                <div class="card-body">
                    <p class="mb-0 text-muted">{{ $return->notes ?: 'Not bulunmuyor.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">İade Kalemleri</div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th>Miktar</th>
                        <th>Neden Kodu</th>
                        <th>Not</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($return->lines as $line)
                        <tr>
                            <td>{{ optional($line->product)->name ?? '—' }}</td>
                            <td>{{ number_format($line->qty, 3, ',', '.') }}</td>
                            <td>{{ $line->reason_code ?? '—' }}</td>
                            <td>{{ $line->notes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
