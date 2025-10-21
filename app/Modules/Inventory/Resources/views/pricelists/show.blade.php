@extends('layouts.admin')

@section('title', $priceList->name)

@section('content')
<x-ui-page-header :title="$priceList->name" description="Fiyat listesi detayları">
    <x-slot name="actions">
        <a href="{{ route('admin.inventory.pricelists.index') }}" class="btn btn-outline-secondary">Listeye Dön</a>
        @can('update', $priceList)
            <x-ui-button variant="primary" href="{{ route('admin.inventory.pricelists.edit', $priceList) }}">Düzenle</x-ui-button>
        @endcan
    </x-slot>
</x-ui-page-header>

@if(session('status'))
    <x-ui-alert type="success" dismissible>{{ session('status') }}</x-ui-alert>
@endif

<div class="row g-4">
    <div class="col-lg-5">
        <x-ui-card>
            <dl class="row mb-0">
                <dt class="col-sm-5 text-muted">Ad</dt>
                <dd class="col-sm-7 fw-semibold">{{ $priceList->name }}</dd>
                <dt class="col-sm-5 text-muted">Tür</dt>
                <dd class="col-sm-7">{{ $priceList->type === 'sale' ? 'Satış' : 'Satın Alma' }}</dd>
                <dt class="col-sm-5 text-muted">Para Birimi</dt>
                <dd class="col-sm-7">{{ strtoupper($priceList->currency) }}</dd>
                <dt class="col-sm-5 text-muted">Durum</dt>
                <dd class="col-sm-7">
                    <x-ui-badge :type="$priceList->active ? 'success' : 'secondary'" soft>{{ $priceList->active ? 'Aktif' : 'Pasif' }}</x-ui-badge>
                </dd>
            </dl>
        </x-ui-card>
        <x-ui-card class="mt-4">
            <h2 class="h6">Yeni Satır</h2>
            @can('update', $priceList)
                <form method="POST" action="{{ route('admin.inventory.pricelists.items.store', $priceList) }}" data-pricelist-item-form>
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="priceListProduct">Ürün</label>
                            <select class="form-select" id="priceListProduct" name="product_id" required data-pricelist-product data-old-value="{{ old('product_id') }}">
                                <option value="">Seçin...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" @selected(old('product_id') == $product->id) data-variants='@json($product->variants->map(fn($variant) => ['id' => $variant->id, 'label' => $variant->sku])->values())'>
                                        {{ $product->sku }} — {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="priceListVariant">Varyant (opsiyonel)</label>
                            <select class="form-select" id="priceListVariant" name="variant_id" data-pricelist-variant data-old-value="{{ old('variant_id') }}">
                                <option value="">Varyant seçin</option>
                            </select>
                            @error('variant_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <x-ui-input type="number" step="0.01" min="0" name="price" label="Fiyat" placeholder="0,00" required :value="old('price')" />
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <x-ui-button type="submit" variant="primary">Satır Ekle</x-ui-button>
                    </div>
                </form>
            @else
                <p class="text-muted mb-0">Bu listede değişiklik yapma yetkiniz yok.</p>
            @endcan
        </x-ui-card>
    </div>
    <div class="col-lg-7">
        <x-ui-card>
            <h2 class="h6 mb-3">Satırlar</h2>
            @if($priceList->items->isEmpty())
                <p class="text-muted mb-0">Henüz bir fiyat satırı eklenmemiş.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th>Varyant</th>
                                <th>Fiyat</th>
                                <th class="text-end">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($priceList->items as $item)
                                <tr>
                                    <td>{{ $item->product?->name ?? 'Silindi' }}</td>
                                    <td>{{ $item->variant?->sku ?? '—' }}</td>
                                    <td>{{ number_format((float) $item->price, 2, ',', '.') }}</td>
                                    <td class="text-end">
                                        @can('update', $priceList)
                                            <form method="POST" action="{{ route('admin.inventory.pricelists.items.destroy', [$priceList, $item]) }}" onsubmit="return confirm('Satırı kaldırmak istediğinize emin misiniz?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui-button type="submit" variant="danger" size="sm">Sil</x-ui-button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui-card>
    </div>
</div>
@endsection
