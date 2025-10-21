@php
    $category = $category ?? null;
    $parents = $parents ?? collect();
@endphp

<div class="row g-4">
    <div class="col-md-4">
        <x-ui-input
            name="code"
            label="Kod"
            :value="old('code', $category?->code)"
            required
            placeholder="C-001"
        />
    </div>
    <div class="col-md-8">
        <x-ui-input
            name="name"
            label="Kategori Adı"
            :value="old('name', $category?->name)"
            required
            placeholder="Kategori adı"
        />
    </div>
    <div class="col-md-6">
        <x-ui-select name="parent_id" label="Üst Kategori">
            <option value="">(Yok)</option>
            @foreach($parents as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $category?->parent_id) == $parent->id)>
                    {{ $parent->name }}
                </option>
            @endforeach
        </x-ui-select>
    </div>
    <div class="col-md-6">
        <x-ui-select name="status" label="Durum" required>
            @php($statusValue = old('status', $category?->status ?? 'active'))
            <option value="active" @selected($statusValue === 'active')>Aktif</option>
            <option value="inactive" @selected($statusValue === 'inactive')>Pasif</option>
        </x-ui-select>
    </div>
</div>
