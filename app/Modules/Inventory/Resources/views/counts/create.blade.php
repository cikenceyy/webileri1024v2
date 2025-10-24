@extends('layouts.admin')

@section('title', 'Sayım Oluştur')
@section('module', 'Inventory')

@section('content')
    <section class="inv-card">
        <header class="inv-card__header d-flex justify-content-between align-items-center">
            <h1 class="inv-card__title">Yeni Stok Sayımı</h1>
            <a href="{{ route('admin.inventory.counts.index') }}" class="btn btn-link btn-sm">← Listeye dön</a>
        </header>
        <form method="post" action="{{ route('admin.inventory.counts.store') }}" class="inv-form">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Belge No</label>
                    <input type="text" name="doc_no" value="{{ old('doc_no', $nextDoc) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Depo</label>
                    <select name="warehouse_id" class="form-select" required>
                        <option value="">Seçiniz</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $defaultWarehouse) == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">Raf</label>
                    <select name="bin_id" class="form-select">
                        <option value="">Depo genel</option>
                        @foreach ($warehouses as $warehouse)
                            <optgroup label="{{ $warehouse->name }}">
                                @foreach ($warehouse->bins as $bin)
                                    <option value="{{ $bin->id }}" @selected(old('bin_id') == $bin->id)>{{ $bin->code }} — {{ $bin->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('bin_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <hr class="my-4">
            <h2 class="h6">Sayım Kalemleri</h2>
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Ürün ID</th>
                        <th>Beklenen</th>
                        <th>Sayılmış</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = old('lines', [
                            ['product_id' => '', 'qty_expected' => '', 'qty_counted' => ''],
                            ['product_id' => '', 'qty_expected' => '', 'qty_counted' => ''],
                            ['product_id' => '', 'qty_expected' => '', 'qty_counted' => ''],
                        ]);
                    @endphp
                    @foreach ($lines as $index => $line)
                        <tr>
                            <td>
                                <input type="number" name="lines[{{ $index }}][product_id]" value="{{ $line['product_id'] }}" class="form-control" min="1">
                                @error("lines.$index.product_id")
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <input type="number" step="0.01" name="lines[{{ $index }}][qty_expected]" value="{{ $line['qty_expected'] }}" class="form-control">
                                @error("lines.$index.qty_expected")
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <input type="number" step="0.01" name="lines[{{ $index }}][qty_counted]" value="{{ $line['qty_counted'] }}" class="form-control" required>
                                @error("lines.$index.qty_counted")
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Taslak Kaydet</button>
                <a href="{{ route('admin.inventory.counts.index') }}" class="btn btn-secondary">İptal</a>
            </div>
        </form>
    </section>
@endsection
