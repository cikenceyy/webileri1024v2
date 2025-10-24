@extends('layouts.admin')

@section('title', 'Transfer Oluştur')
@section('module', 'Inventory')

@section('content')
    <section class="inv-card">
        <header class="inv-card__header d-flex justify-content-between align-items-center">
            <h1 class="inv-card__title">Yeni Stok Transferi</h1>
            <a href="{{ route('admin.inventory.transfers.index') }}" class="btn btn-link btn-sm">← Listeye dön</a>
        </header>
        <form method="post" action="{{ route('admin.inventory.transfers.store') }}" class="inv-form">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Belge No</label>
                    <input type="text" name="doc_no" value="{{ old('doc_no', $nextDoc) }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kaynak Depo</label>
                    <select name="from_warehouse_id" class="form-select" required>
                        <option value="">Seçiniz</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('from_warehouse_id', $defaultWarehouse) == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('from_warehouse_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kaynak Raf</label>
                    <select name="from_bin_id" class="form-select">
                        <option value="">Depo genel</option>
                        @foreach ($warehouses as $warehouse)
                            <optgroup label="{{ $warehouse->name }}">
                                @foreach ($warehouse->bins as $bin)
                                    <option value="{{ $bin->id }}" @selected(old('from_bin_id') == $bin->id)>{{ $bin->code }} — {{ $bin->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('from_bin_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hedef Depo</label>
                    <select name="to_warehouse_id" class="form-select" required>
                        <option value="">Seçiniz</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('to_warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('to_warehouse_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hedef Raf</label>
                    <select name="to_bin_id" class="form-select">
                        <option value="">Depo genel</option>
                        @foreach ($warehouses as $warehouse)
                            <optgroup label="{{ $warehouse->name }}">
                                @foreach ($warehouse->bins as $bin)
                                    <option value="{{ $bin->id }}" @selected(old('to_bin_id') == $bin->id)>{{ $bin->code }} — {{ $bin->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('to_bin_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <hr class="my-4">
            <h2 class="h6">Satırlar</h2>
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Ürün ID</th>
                        <th>Miktar</th>
                        <th>Not</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = old('lines', [
                            ['product_id' => '', 'qty' => '', 'note' => ''],
                            ['product_id' => '', 'qty' => '', 'note' => ''],
                            ['product_id' => '', 'qty' => '', 'note' => ''],
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
                                <input type="number" step="0.01" name="lines[{{ $index }}][qty]" value="{{ $line['qty'] }}" class="form-control" min="0.01">
                                @error("lines.$index.qty")
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <input type="text" name="lines[{{ $index }}][note]" value="{{ $line['note'] }}" class="form-control">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Taslak Kaydet</button>
                <a href="{{ route('admin.inventory.transfers.index') }}" class="btn btn-secondary">İptal</a>
            </div>
        </form>
    </section>
@endsection
