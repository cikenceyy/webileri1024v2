@extends('layouts.admin')

@section('title', 'İş Emri Düzenle')
@section('module', 'Production')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">İş Emri Düzenle</h1>
        <a href="{{ route('admin.production.workorders.show', $workOrder) }}" class="btn btn-outline-secondary">Geri</a>
    </div>

    <form action="{{ route('admin.production.workorders.update', $workOrder) }}" method="post" class="card">
        @csrf
        @method('put')
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label" for="product_id">Ürün</label>
                    <select name="product_id" id="product_id" class="form-select" required>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id', $workOrder->product_id) == $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="bom_id">BOM</label>
                    <select name="bom_id" id="bom_id" class="form-select" required>
                        @foreach($boms as $bom)
                            <option value="{{ $bom->id }}" @selected(old('bom_id', $workOrder->bom_id) == $bom->id)>{{ $bom->code }} • {{ $bom->product?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="target_qty">Hedef Miktar</label>
                    <input type="number" step="0.001" name="target_qty" id="target_qty" class="form-control" value="{{ old('target_qty', $workOrder->target_qty) }}" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label" for="uom">Birim</label>
                    <input type="text" name="uom" id="uom" class="form-control" value="{{ old('uom', $workOrder->uom) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="due_date">Termin Tarihi</label>
                    <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date', optional($workOrder->due_date)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="notes">Not</label>
                    <textarea name="notes" id="notes" rows="3" class="form-control">{{ old('notes', $workOrder->notes) }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </div>
    </form>
@endsection
