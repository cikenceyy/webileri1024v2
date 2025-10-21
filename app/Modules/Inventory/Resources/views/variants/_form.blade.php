@php
    $variant = $variant ?? null;
    $optionRows = old('options', $variant?->options ?? []);
    if (! is_array($optionRows)) {
        $optionRows = [];
    }
@endphp

<div class="row g-4">
    <div class="col-md-4">
        <x-ui-input name="sku" label="SKU" :value="old('sku', $variant?->sku)" required />
    </div>
    <div class="col-md-4">
        <x-ui-input name="barcode" label="Barkod" :value="old('barcode', $variant?->barcode)" />
    </div>
    <div class="col-md-4">
        <x-ui-select name="status" label="Durum" required>
            @php($statusValue = old('status', $variant?->status ?? 'active'))
            <option value="active" @selected($statusValue === 'active')>Aktif</option>
            <option value="inactive" @selected($statusValue === 'inactive')>Pasif</option>
        </x-ui-select>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Opsiyonlar</label>
        <div class="border rounded p-3 bg-light" data-variant-options>
            <div data-variant-option-list>
                @forelse($optionRows as $key => $value)
                    <div class="row g-2 align-items-center mb-2" data-variant-option-row>
                        <div class="col-md-4">
                            <x-ui-input name="option_keys[]" label="Opsiyon Adı" :value="$key" data-variant-option-key />
                        </div>
                        <div class="col-md-6">
                            <x-ui-input name="option_values[]" label="Değer" :value="$value" data-variant-option-value />
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger" data-action="remove-variant-option">Sil</button>
                        </div>
                    </div>
                @empty
                    <div class="row g-2 align-items-center mb-2" data-variant-option-row>
                        <div class="col-md-4">
                            <x-ui-input name="option_keys[]" label="Opsiyon Adı" data-variant-option-key />
                        </div>
                        <div class="col-md-6">
                            <x-ui-input name="option_values[]" label="Değer" data-variant-option-value />
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger" data-action="remove-variant-option">Sil</button>
                        </div>
                    </div>
                @endforelse
            </div>
            <template data-variant-option-template>
                <div class="row g-2 align-items-center mb-2" data-variant-option-row>
                    <div class="col-md-4">
                        <x-ui-input name="option_keys[]" label="Opsiyon Adı" data-variant-option-key />
                    </div>
                    <div class="col-md-6">
                        <x-ui-input name="option_values[]" label="Değer" data-variant-option-value />
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger" data-action="remove-variant-option">Sil</button>
                    </div>
                </div>
            </template>
            <div class="mt-2" data-variant-option-actions>
                <x-ui-button type="button" variant="outline" size="sm" data-action="add-variant-option">Opsiyon Satırı Ekle</x-ui-button>
            </div>
        </div>
        <p class="text-muted small mt-2 mb-0">Örn. renk=Kırmızı, beden=L. Opsiyon adları benzersiz olmalıdır.</p>
    </div>
</div>
