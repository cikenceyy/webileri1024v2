@php use Illuminate\Support\Str; @endphp
@extends('layouts.admin')

@section('title', $transfer->doc_no)
@section('module', 'Inventory')

@section('content')
    <section class="inv-card">
        <header class="inv-card__header d-flex justify-content-between align-items-center">
            <div>
                <h1 class="inv-card__title">Transfer {{ $transfer->doc_no }}</h1>
                <p class="text-muted">Durum: <span class="badge bg-{{ $transfer->status === 'posted' ? 'success' : 'secondary' }}">{{ ucfirst($transfer->status) }}</span></p>
            </div>
            <a href="{{ route('admin.inventory.transfers.index') }}" class="btn btn-link btn-sm">← Listeye dön</a>
        </header>
        <dl class="row">
            <dt class="col-sm-3">Kaynak Depo</dt>
            <dd class="col-sm-9">{{ $transfer->fromWarehouse?->name }} @if($transfer->fromBin) <span class="text-muted">({{ $transfer->fromBin->code }})</span>@endif</dd>
            <dt class="col-sm-3">Hedef Depo</dt>
            <dd class="col-sm-9">{{ $transfer->toWarehouse?->name }} @if($transfer->toBin) <span class="text-muted">({{ $transfer->toBin->code }})</span>@endif</dd>
            <dt class="col-sm-3">Oluşturan</dt>
            <dd class="col-sm-9">{{ optional($transfer->created_at)->format('d.m.Y H:i') }}</dd>
            @if($transfer->posted_at)
                <dt class="col-sm-3">Gönderim</dt>
                <dd class="col-sm-9">{{ $transfer->posted_at->format('d.m.Y H:i') }}</dd>
            @endif
        </dl>
        <h2 class="h6 mt-4">Satırlar</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Ürün</th>
                    <th class="text-end">Miktar</th>
                    <th>Not</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transfer->lines as $line)
                    <tr>
                        <td>{{ $line->product?->name ?? ('#' . $line->product_id) }}</td>
                        <td class="text-end">{{ number_format($line->qty, 2) }}</td>
                        <td>{{ $line->note }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if ($transfer->status === 'draft')
            <form method="post" action="{{ route('admin.inventory.transfers.post', $transfer) }}" onsubmit="return confirm('Transferi göndermek istediğinize emin misiniz?')">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ (string) Str::uuid() }}">
                <button type="submit" class="btn btn-success">Transferi Gönder</button>
            </form>
        @endif
    </section>
@endsection
