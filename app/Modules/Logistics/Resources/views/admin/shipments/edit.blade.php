@extends('layouts.admin')

@section('title', 'Sevkiyatı Düzenle')
@section('module', 'Logistics')

@section('content')
    <form method="post" action="{{ route('admin.logistics.shipments.update', $shipment) }}">
        @csrf
        @method('put')

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Sevkiyat #{{ $shipment->doc_no }}</h1>
                <span class="badge bg-light text-dark text-capitalize">{{ $shipment->status }}</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.logistics.shipments.show', $shipment) }}" class="btn btn-outline-secondary">Vazgeç</a>
                <button class="btn btn-primary">Güncelle</button>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Müşteri</label>
                <select name="customer_id" class="form-select">
                    <option value="">Seçiniz</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id', $shipment->customer_id) == $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Depo</label>
                <select name="warehouse_id" class="form-select">
                    <option value="">Seçiniz</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $shipment->warehouse_id) == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Paket Sayısı</label>
                <input type="number" name="packages_count" value="{{ old('packages_count', $shipment->packages_count) }}" class="form-control" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label">Brüt Ağırlık</label>
                <input type="number" step="0.001" min="0" name="gross_weight" value="{{ old('gross_weight', $shipment->gross_weight) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Net Ağırlık</label>
                <input type="number" step="0.001" min="0" name="net_weight" value="{{ old('net_weight', $shipment->net_weight) }}" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label">Not</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $shipment->notes) }}</textarea>
            </div>
        </div>

        <hr class="my-4">

        <h2 class="h5 mb-3">Satır Detayları</h2>
        <div class="table-responsive">
            <table class="table align-middle" id="shipment-lines">
                <thead>
                    <tr>
                        <th style="width:25%">Ürün</th>
                        <th style="width:12%">Varyant ID</th>
                        <th style="width:12%">Miktar</th>
                        <th style="width:12%">Birim</th>
                        <th>Açıklama</th>
                        <th style="width:5%"></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $oldLines = old('lines', $shipment->lines->map(fn ($line) => [
                            'id' => $line->id,
                            'product_id' => $line->product_id,
                            'variant_id' => $line->variant_id,
                            'qty' => $line->qty,
                            'uom' => $line->uom,
                            'notes' => $line->notes,
                        ])->toArray());
                    @endphp
                    @foreach ($oldLines as $index => $line)
                        <tr>
                            <td>
                                <input type="hidden" name="lines[{{ $index }}][id]" value="{{ $line['id'] ?? '' }}">
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
                                <input type="number" step="0.001" min="0.001" name="lines[{{ $index }}][qty]" value="{{ $line['qty'] ?? 1 }}" class="form-control" required>
                            </td>
                            <td>
                                <input type="text" name="lines[{{ $index }}][uom]" value="{{ $line['uom'] ?? 'pcs' }}" class="form-control" maxlength="16">
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
        <button type="button" class="btn btn-sm btn-outline-primary" id="add-shipment-line">Satır Ekle</button>
    </form>
@endsection

@push('scripts')
    <script>
        document.getElementById('add-shipment-line').addEventListener('click', function () {
            const table = document.querySelector('#shipment-lines tbody');
            const index = table.rows.length;
            const template = `
                <tr>
                    <td>
                        <input type="hidden" name="lines[${index}][id]" value="">
                        <select name="lines[${index}][product_id]" class="form-select" required>
                            <option value="">Seçiniz</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="lines[${index}][variant_id]" class="form-control" min="1"></td>
                    <td><input type="number" step="0.001" min="0.001" name="lines[${index}][qty]" class="form-control" value="1" required></td>
                    <td><input type="text" name="lines[${index}][uom]" class="form-control" value="pcs" maxlength="16"></td>
                    <td><input type="text" name="lines[${index}][notes]" class="form-control"></td>
                    <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-line>&times;</button></td>
                </tr>`;
            table.insertAdjacentHTML('beforeend', template);
        });

        document.querySelector('#shipment-lines').addEventListener('click', function (event) {
            if (event.target.matches('[data-remove-line]')) {
                const row = event.target.closest('tr');
                row.remove();
            }
        });
    </script>
@endpush
