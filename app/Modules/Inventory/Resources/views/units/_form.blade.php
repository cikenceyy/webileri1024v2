@php
    $unit = $unit ?? null;
@endphp

<div class="row g-4">
    <div class="col-md-4">
        <x-ui-input name="code" label="Kod" :value="old('code', $unit?->code)" required placeholder="PCS" />
    </div>
    <div class="col-md-8">
        <x-ui-input name="name" label="Ad" :value="old('name', $unit?->name)" required placeholder="Parça" />
    </div>
    <div class="col-md-6">
        <x-ui-input type="number" name="to_base" label="Temel birime dönüşüm" step="0.000001" min="0.000001" :value="old('to_base', $unit?->to_base ?? 1)" required />
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="unitIsBase" name="is_base" value="1" @checked(old('is_base', $unit?->is_base))>
            <label class="form-check-label" for="unitIsBase">Temel birim olarak ayarla</label>
        </div>
    </div>
</div>
