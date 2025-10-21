@php
    $warehouse = $warehouse ?? null;
@endphp

<div class="row g-4">
    <div class="col-md-4">
        <x-ui-input name="code" label="Kod" :value="old('code', $warehouse?->code)" required placeholder="MAIN" />
    </div>
    <div class="col-md-8">
        <x-ui-input name="name" label="Ambar Adı" :value="old('name', $warehouse?->name)" required placeholder="Ana Ambar" />
    </div>
    <div class="col-md-6">
        <x-ui-select name="status" label="Durum" required>
            @php($statusValue = old('status', $warehouse?->status ?? 'active'))
            <option value="active" @selected($statusValue === 'active')>Aktif</option>
            <option value="inactive" @selected($statusValue === 'inactive')>Pasif</option>
        </x-ui-select>
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_default" id="warehouseDefault" value="1" @checked(old('is_default', $warehouse?->is_default))>
            <label class="form-check-label" for="warehouseDefault">Varsayılan ambar</label>
        </div>
    </div>
</div>
