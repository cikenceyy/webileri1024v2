@extends('layouts.admin')

@section('content')
    <x-ui-page-header title="Stok Çıkışı" description="Satış veya sevkiyat için stok düşüşü yapın." />

    <x-ui-card>
        <form method="post" action="{{ route('admin.inventory.stock.out.store') }}" class="row g-4">
            @csrf
            <div class="col-md-6">
                <label class="form-label">Ambar</label>
                <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                    <option value="">Seçin</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
                @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Ürün</label>
                <select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                    <option value="">Seçin</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->sku }} — {{ $product->name }}</option>
                    @endforeach
                </select>
                @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Varyant (opsiyonel)</label>
                <select name="variant_id" class="form-select @error('variant_id') is-invalid @enderror">
                    <option value="">Varsayılan</option>
                    @foreach($variants as $variant)
                        <option value="{{ $variant->id }}" @selected(old('variant_id') == $variant->id)>{{ $variant->sku }} — {{ $variant->product?->name }}</option>
                    @endforeach
                </select>
                @error('variant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <x-ui-input type="number" step="0.001" min="0" name="qty" label="Miktar" value="{{ old('qty') }}" required />
            </div>
            <div class="col-md-3">
                <label class="form-label">İşlem Nedeni</label>
                <select name="reason" class="form-select">
                    <option value="">Seçin</option>
                    @foreach($reasons as $reason)
                        <option value="{{ $reason }}" @selected(old('reason') === $reason)>{{ ucfirst(str_replace('_', ' ', $reason)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <x-ui-input type="date" name="moved_at" label="İşlem Tarihi" value="{{ old('moved_at') }}" />
            </div>
            <div class="col-md-3">
                <x-ui-input name="ref_type" label="Referans Türü" value="{{ old('ref_type') }}" />
            </div>
            <div class="col-md-3">
                <x-ui-input name="ref_id" label="Referans ID" value="{{ old('ref_id') }}" />
            </div>
            <div class="col-12">
                <x-ui-textarea name="note" label="Not" rows="3">{{ old('note') }}</x-ui-textarea>
            </div>
            <div class="col-12 d-flex justify-content-between">
                <a href="{{ route('admin.inventory.stock.index') }}" class="btn btn-light">Vazgeç</a>
                <button type="submit" class="btn btn-danger">Stok Çıkışını Kaydet</button>
            </div>
        </form>
    </x-ui-card>
@endsection
