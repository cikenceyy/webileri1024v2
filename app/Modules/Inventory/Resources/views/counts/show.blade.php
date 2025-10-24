@php use Illuminate\Support\Str; @endphp
@extends('layouts.admin')

@section('title', $count->doc_no)
@section('module', 'Inventory')

@section('content')
    <section class="inv-card">
        <header class="inv-card__header d-flex justify-content-between align-items-center">
            <div>
                <h1 class="inv-card__title">Sayım {{ $count->doc_no }}</h1>
                <p class="text-muted">Durum: <span class="badge bg-{{ $count->status === 'reconciled' ? 'success' : ($count->status === 'counted' ? 'warning' : 'secondary') }}">{{ ucfirst($count->status) }}</span></p>
            </div>
            <a href="{{ route('admin.inventory.counts.index') }}" class="btn btn-link btn-sm">← Listeye dön</a>
        </header>
        <dl class="row">
            <dt class="col-sm-3">Depo</dt>
            <dd class="col-sm-9">{{ $count->warehouse?->name }}</dd>
            <dt class="col-sm-3">Raf</dt>
            <dd class="col-sm-9">{{ $count->bin?->code ?? 'Genel' }}</dd>
            <dt class="col-sm-3">Oluşturulma</dt>
            <dd class="col-sm-9">{{ optional($count->created_at)->format('d.m.Y H:i') }}</dd>
            @if($count->counted_at)
                <dt class="col-sm-3">Sayım Tarihi</dt>
                <dd class="col-sm-9">{{ $count->counted_at->format('d.m.Y H:i') }}</dd>
            @endif
            @if($count->reconciled_at)
                <dt class="col-sm-3">Mutabakat</dt>
                <dd class="col-sm-9">{{ $count->reconciled_at->format('d.m.Y H:i') }}</dd>
            @endif
        </dl>
        <h2 class="h6 mt-4">Kalemler</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Ürün</th>
                    <th class="text-end">Beklenen</th>
                    <th class="text-end">Sayılmış</th>
                    <th class="text-end">Fark</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($count->lines as $line)
                    <tr>
                        <td>{{ $line->product?->name ?? ('#' . $line->product_id) }}</td>
                        <td class="text-end">{{ number_format($line->qty_expected ?? 0, 2) }}</td>
                        <td class="text-end">{{ number_format($line->qty_counted ?? 0, 2) }}</td>
                        <td class="text-end">{{ number_format($line->diff_cached ?? (($line->qty_counted ?? 0) - ($line->qty_expected ?? 0)), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex gap-2">
            @if ($count->status === 'draft')
                <form method="post" action="{{ route('admin.inventory.counts.mark-counted', $count) }}">
                    @csrf
                    @method('patch')
                    <button type="submit" class="btn btn-outline-primary">Counted olarak işaretle</button>
                </form>
            @endif
            @if ($count->status !== 'reconciled')
                <form method="post" action="{{ route('admin.inventory.counts.reconcile', $count) }}" onsubmit="return confirm('Sayımı mutabık etmek istediğinize emin misiniz?')">
                    @csrf
                    @method('patch')
                    <input type="hidden" name="idempotency_key" value="{{ (string) Str::uuid() }}">
                    <button type="submit" class="btn btn-success">Mutabık Et</button>
                </form>
            @endif
        </div>
    </section>
@endsection
