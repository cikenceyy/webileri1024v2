@extends('layouts.admin')

@section('title', 'Yeni Mal Kabul')
@section('module', 'Logistics')

@section('content')
    <form method="post" action="{{ route('admin.logistics.receipts.store') }}">
        @csrf
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Yeni Mal Kabul</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.logistics.receipts.index') }}" class="btn btn-outline-secondary">Geri</a>
                <button class="btn btn-primary">Kaydet</button>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tedarikçi (ID)</label>
                <input type="number" name="vendor_id" value="{{ old('vendor_id') }}" class="form-control" min="1">
            </div>
            <div class="col-md-4">
                <label class="form-label">Depo</label>
                <select name="warehouse_id" class="form-select">
                    <option value="">Seçiniz</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $defaults['warehouse_id']) == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Not</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>

        <hr class="my-4">

        <h2 class="h5 mb-3">Satır Detayları</h2>
        <div class="table-responsive">
            <table class="table align-middle" id="receipt-lines">
                <thead>
                    <tr>
                        <th style="width:25%">Ürün</th>
                        <th style="width:12%">Varyant ID</th>
                        <th style="width:12%">Beklenen</th>
                        <th style="width:12%">Not</th>
                        <th style="width:5%"></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $oldLines = old('lines', [['product_id' => null, 'qty_expected' => 0]]);
                    @endphp
                    @foreach ($oldLines as $index => $line)
                        <tr>
                            <td>
                                <select name="lines[{{ $index }}][product_id]" class="form-select" required>
                                    <option value="">Seçiniz</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" @selected(($line['product_id'] ?? null) == $product->id)>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="lines[{{ $index }}][variant_id]" value="{{ $line['variant_id'] ?? '' }}" class="form-control" min="1">
                            </td>
                            <td>
                                <input type="number" step="0.001" min="0" name="lines[{{ $index }}][qty_expected]" value="{{ $line['qty_expected'] ?? 0 }}" class="form-control">
                            </td>
                            <td>
                                <input type="text" name="lines[{{ $index }}][notes]" value="{{ $line['notes'] ?? '' }}" class="form-control">
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger" data-remove-line>&times;</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="add-receipt-line">Satır Ekle</button>
    </form>
@endsection

@push('scripts')
    <script>
        document.getElementById('add-receipt-line').addEventListener('click', function () {
            const table = document.querySelector('#receipt-lines tbody');
            const index = table.rows.length;
            const template = `
                <tr>
                    <td>
                        <select name="lines[${index}][product_id]" class="form-select" required>
                            <option value="">Seçiniz</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="lines[${index}][variant_id]" class="form-control" min="1"></td>
                    <td><input type="number" step="0.001" min="0" name="lines[${index}][qty_expected]" class="form-control" value="0"></td>
                    <td><input type="text" name="lines[${index}][notes]" class="form-control"></td>
                    <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-line>&times;</button></td>
                </tr>`;
            table.insertAdjacentHTML('beforeend', template);
        });

        document.querySelector('#receipt-lines').addEventListener('click', function (event) {
            if (event.target.matches('[data-remove-line]')) {
                event.target.closest('tr').remove();
            }
        });
    </script>
@endpush
