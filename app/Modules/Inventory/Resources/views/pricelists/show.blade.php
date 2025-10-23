@extends('layouts.admin')

@section('title', $pricelist->name)
@section('module', 'Inventory')

@push('page-styles')
    @vite('app/Modules/Inventory/Resources/scss/pricelists.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Inventory/Resources/js/pricelists.js')
@endpush

@section('content')
    <section class="inv-prices" data-pricelist="{{ $pricelist->id }}">
        <header class="inv-prices__header">
            <h1 class="inv-prices__title">{{ $pricelist->name }}</h1>
            <span class="inv-badge">{{ $pricelist->currency }}</span>
        </header>

        <section class="inv-prices__sim" data-simulation>
            <h2 class="inv-prices__section-title">Mini Simülasyon</h2>
            <form class="inv-prices__sim-form">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Ürün Sayısı</label>
                        <input type="number" class="form-control" min="1" value="5" data-sim-products>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">İskonto %</label>
                        <input type="number" class="form-control" min="0" max="100" value="0" data-sim-discount>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-primary" data-action="simulate">Hesapla</button>
                    </div>
                </div>
                <p class="inv-prices__sim-result" data-sim-result>Sonuç bekleniyor…</p>
            </form>
        </section>

        <section class="inv-prices__items" data-items>
            <h2 class="inv-prices__section-title">Kalemler</h2>
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th>Fiyat</th>
                        <th class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pricelist->items as $item)
                        <tr data-item="{{ $item->id }}">
                            <td>{{ $item->product?->name ?? 'Ürün' }}</td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" value="{{ $item->price }}" step="0.01" data-field="price">
                                    <span class="input-group-text">{{ $pricelist->currency }}</span>
                                </div>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-action="item-save">Kaydet</button>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-action="item-remove">Sil</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="button" class="btn btn-outline-primary" data-action="item-add">Yeni Kalem Ekle</button>
        </section>
    </section>
@endsection
