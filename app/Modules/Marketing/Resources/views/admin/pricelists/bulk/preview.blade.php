@extends('layouts.admin')

@section('title', 'Toplu Güncelleme Önizleme')
@section('module', 'Marketing')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.marketing.pricelists.bulk.form', $pricelist) }}" class="btn btn-link p-0 me-3">&larr; Ayarları değiştir</a>
        <h1 class="h3 mb-0">{{ $pricelist->name }} • Önizleme</h1>
    </div>

    @if ($changes->isEmpty())
        <div class="alert alert-info">Seçilen kriterlere göre fiyat değişikliği gerekmiyor.</div>
    @else
        <form method="post" action="{{ route('admin.marketing.pricelists.bulk.apply', $pricelist) }}" class="card">
            @csrf
            @foreach ($filters as $key => $value)
                @if($value !== null && $value !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            @foreach ($operation as $key => $value)
                <input type="hidden" name="operation[{{ $key }}]" value="{{ $value }}">
            @endforeach
            <div class="card-body">
                <p class="text-muted">{{ $changes->count() }} satır güncellenecek.</p>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th>Eski Fiyat</th>
                                <th>Yeni Fiyat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($changes as $change)
                                <tr>
                                    <td>{{ optional($change['item']->product)->name ?? 'Ürün #' . $change['item']->product_id }}</td>
                                    <td>{{ number_format($change['old_price'], 2, ',', '.') }}</td>
                                    <td>{{ number_format($change['new_price'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary">Değişiklikleri Uygula</button>
            </div>
        </form>
    @endif
@endsection
