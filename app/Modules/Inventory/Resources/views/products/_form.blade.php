@php
    $product = $product ?? null;
    $selectedMedia = $selectedMedia ?? null;
    $categories = $categories ?? collect();
    $units = $units ?? collect();
@endphp

<div class="row g-4">
    <div class="col-md-6">
        <x-ui-input
            name="sku"
            label="SKU"
            :value="old('sku', $product?->sku)"
            required
            placeholder="Örn. PROD-001"
        />
    </div>
    <div class="col-md-6">
        <x-ui-input
            name="name"
            label="Ürün Adı"
            :value="old('name', $product?->name)"
            required
            placeholder="Ürünün kısa adı"
        />
    </div>
    <div class="col-md-4">
        <x-ui-select name="category_id" label="Kategori">
            <option value="">(Belirtilmemiş)</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </x-ui-select>
    </div>
    <div class="col-md-4">
        <x-ui-input
            name="barcode"
            label="Barkod"
            :value="old('barcode', $product?->barcode)"
            placeholder="Opsiyonel barkod"
        />
    </div>
    <div class="col-md-4">
        <x-ui-select name="base_unit_id" label="Temel Birim">
            <option value="">(Varsayılan)</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" @selected(old('base_unit_id', $product?->base_unit_id) == $unit->id)>
                    {{ $unit->code }} — {{ $unit->name }}
                </option>
            @endforeach
        </x-ui-select>
    </div>
    <div class="col-md-4">
        <x-ui-input
            type="number"
            step="0.01"
            min="0"
            name="price"
            label="Varsayılan Fiyat"
            :value="old('price', $product?->price)"
            placeholder="0,00"
        />
    </div>
    <div class="col-md-4">
        <x-ui-input
            name="unit"
            label="Görünen Birim"
            :value="old('unit', $product?->unit ?? config('inventory.default_unit'))"
            placeholder="pcs"
        />
    </div>
    <div class="col-md-4">
        <x-ui-input
            type="number"
            step="0.001"
            min="0"
            name="reorder_point"
            label="Yeniden Sipariş Noktası"
            :value="old('reorder_point', $product?->reorder_point)"
            placeholder="0"
        />
    </div>
    <div class="col-md-4">
        <x-ui-select name="status" label="Durum" required>
            @php($statusValue = old('status', $product?->status ?? 'active'))
            <option value="active" @selected($statusValue === 'active')>Aktif</option>
            <option value="inactive" @selected($statusValue === 'inactive')>Pasif</option>
        </x-ui-select>
    </div>
    <div class="col-12">
        <x-ui-textarea
            name="description"
            label="Açıklama"
            rows="4"
            placeholder="Ürün detaylarını ekleyin"
        >{{ old('description', $product?->description) }}</x-ui-textarea>
    </div>
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label fw-semibold mb-0" for="productMediaId">Kapak Görseli</label>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-action="open-drive-picker">
                    Sürücüden Seç
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-action="clear-media">
                    Temizle
                </button>
            </div>
        </div>
        <input
            type="hidden"
            name="media_id"
            id="productMediaId"
            value="{{ old('media_id', $selectedMedia?->id) }}"
            data-product-media-input
        >
        <div
            class="border rounded p-3 d-flex align-items-center gap-3 bg-light"
            data-product-media-preview
            data-empty-message="Drive içinden bir kapak görseli seçin. “Ürün Görselleri” kategorisindeki öğeler kullanılabilir."
            data-state="{{ $selectedMedia ? 'filled' : 'empty' }}"
        >
            @if($selectedMedia)
                <div class="inventory-media-preview">
                    <x-ui-file-icon :ext="$selectedMedia->ext" size="36" />
                    <div class="inventory-media-preview__meta">
                        <div class="inventory-media-preview__name">{{ $selectedMedia->original_name }}</div>
                        <div class="inventory-media-preview__desc">{{ $selectedMedia->mime }} · {{ number_format(($selectedMedia->size ?? 0) / 1024, 1, ',', '.') }} KB</div>
                    </div>
                </div>
            @else
                <div class="inventory-media-empty">Drive içinden bir kapak görseli seçin. “Ürün Görselleri” kategorisindeki öğeler kullanılabilir.</div>
            @endif
        </div>
        @error('media_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
