@php
    $existingItems = collect(old('items', $bom->items->map(fn ($item) => [
        'component_product_id' => $item->component_product_id,
        'qty_per' => $item->qty_per,
        'wastage_pct' => $item->wastage_pct,
        'default_warehouse_id' => $item->default_warehouse_id,
        'default_bin_id' => $item->default_bin_id,
    ])->toArray()))
        ->values();
    if ($existingItems->isEmpty()) {
        $existingItems = collect([[
            'component_product_id' => null,
            'qty_per' => 1,
            'wastage_pct' => 0,
            'default_warehouse_id' => null,
            'default_bin_id' => null,
        ]]);
    }
@endphp

<div class="row mb-3">
    <div class="col-md-6">
        <label for="product_id" class="form-label">Ürün</label>
        <select name="product_id" id="product_id" class="form-select" required>
            <option value="">Seçiniz</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" @selected(old('product_id', $bom->product_id) == $product->id)>
                    {{ $product->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label for="code" class="form-label">Kod</label>
        <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $bom->code) }}" required>
    </div>
    <div class="col-md-3">
        <label for="version" class="form-label">Versiyon</label>
        <input type="number" name="version" id="version" class="form-control" value="{{ old('version', $bom->version) }}" min="1">
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-3">
        <label for="output_qty" class="form-label">Çıktı Miktarı</label>
        <input type="number" step="0.001" name="output_qty" id="output_qty" class="form-control" value="{{ old('output_qty', $bom->output_qty ?: 1) }}" required>
    </div>
    <div class="col-md-3 d-flex align-items-center">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" @checked(old('is_active', $bom->is_active ?? true))>
            <label class="form-check-label" for="is_active">Aktif</label>
        </div>
    </div>
    <div class="col-md-6">
        <label for="notes" class="form-label">Notlar</label>
        <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $bom->notes) }}</textarea>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Bileşenler</span>
        <button type="button" class="btn btn-sm btn-outline-primary" id="bom-add-row">Satır Ekle</button>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0" id="bom-items-table">
            <thead>
            <tr>
                <th style="width: 25%">Malzeme</th>
                <th style="width: 10%">Miktar</th>
                <th style="width: 10%">Fire %</th>
                <th style="width: 20%">Varsayılan Depo</th>
                <th style="width: 20%">Varsayılan Raf</th>
                <th class="text-end"></th>
            </tr>
            </thead>
            <tbody id="bom-items-body">
            @foreach($existingItems as $index => $item)
                <tr>
                    <td>
                        <select name="items[{{ $index }}][component_product_id]" class="form-select" required>
                            <option value="">Seçiniz</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected($item['component_product_id'] == $product->id)>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[{{ $index }}][qty_per]" step="0.001" class="form-control" value="{{ $item['qty_per'] }}" required>
                    </td>
                    <td>
                        <input type="number" name="items[{{ $index }}][wastage_pct]" step="0.01" class="form-control" value="{{ $item['wastage_pct'] }}">
                    </td>
                    <td>
                        <select name="items[{{ $index }}][default_warehouse_id]" class="form-select">
                            <option value="">Seçiniz</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected($item['default_warehouse_id'] == $warehouse->id)>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="items[{{ $index }}][default_bin_id]" class="form-select">
                            <option value="">Seçiniz</option>
                            @foreach($bins as $bin)
                                <option value="{{ $bin->id }}" @selected($item['default_bin_id'] == $bin->id)>{{ $bin->code }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();">Sil</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('page-scripts')
    <script>
        document.getElementById('bom-add-row').addEventListener('click', function () {
            const body = document.getElementById('bom-items-body');
            const index = body.querySelectorAll('tr').length;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <select name="items[${index}][component_product_id]" class="form-select" required>
                        <option value="">Seçiniz</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${index}][qty_per]" step="0.001" class="form-control" value="1" required>
                </td>
                <td>
                    <input type="number" name="items[${index}][wastage_pct]" step="0.01" class="form-control" value="0">
                </td>
                <td>
                    <select name="items[${index}][default_warehouse_id]" class="form-select">
                        <option value="">Seçiniz</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="items[${index}][default_bin_id]" class="form-select">
                        <option value="">Seçiniz</option>
                        @foreach($bins as $bin)
                            <option value="{{ $bin->id }}">{{ $bin->code }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();">Sil</button>
                </td>
            `;
            body.appendChild(row);
        });
    </script>
@endpush
