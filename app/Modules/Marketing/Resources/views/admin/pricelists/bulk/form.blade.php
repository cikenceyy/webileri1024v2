@extends('layouts.admin')

@section('title', 'Fiyat Listesi Toplu Güncelle')
@section('module', 'Marketing')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.marketing.pricelists.show', $pricelist) }}" class="btn btn-link p-0 me-3">&larr; Listeye dön</a>
        <h1 class="h3 mb-0">{{ $pricelist->name }} • Toplu Güncelle</h1>
    </div>

    <form method="post" action="{{ route('admin.marketing.pricelists.bulk.preview', $pricelist) }}" class="card">
        @csrf
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Kategori Filtresi</label>
                    <input type="number" name="category_id" value="{{ old('category_id') }}" class="form-control" placeholder="Kategori ID (opsiyonel)">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ürün Arama</label>
                    <input type="text" name="search" value="{{ old('search') }}" class="form-control" placeholder="Ürün adı veya SKU">
                </div>
            </div>

            <h2 class="h5 mb-3">İşlem</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tür</label>
                    <select name="operation[type]" class="form-select" required>
                        <option value="percent" @selected(old('operation.type') === 'percent')>% Artış / Azalış</option>
                        <option value="fixed" @selected(old('operation.type') === 'fixed')>Sabit Tutar +/-</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Yön</label>
                    <select name="operation[mode]" class="form-select" required>
                        <option value="increase" @selected(old('operation.mode', 'increase') === 'increase')>Artır</option>
                        <option value="decrease" @selected(old('operation.mode') === 'decrease')>Azalt</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Değer</label>
                    <input type="number" step="0.01" name="operation[value]" value="{{ old('operation.value', 0) }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Yuvarlama</label>
                    <select name="operation[round]" class="form-select">
                        <option value="0">Yok</option>
                        <option value="0.05" @selected(old('operation.round') == 0.05)>0.05</option>
                        <option value="0.1" @selected(old('operation.round') == 0.1)>0.10</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-primary">Önizleme Yap</button>
        </div>
    </form>
@endsection
