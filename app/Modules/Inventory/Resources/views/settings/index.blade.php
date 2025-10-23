@extends('layouts.admin')

@section('title', 'Envanter Ayarları')
@section('module', 'Inventory')

@push('page-styles')
    @vite('app/Modules/Inventory/Resources/scss/settings.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Inventory/Resources/js/settings.js')
@endpush

@section('content')
    <section class="inv-settings" data-settings>
        <header class="inv-settings__tabs" role="tablist">
            <button type="button" class="inv-settings__tab is-active" data-settings-tab="categories">Kategoriler</button>
            <button type="button" class="inv-settings__tab" data-settings-tab="variants">Varyant Setleri</button>
            <button type="button" class="inv-settings__tab" data-settings-tab="units">Birimler</button>
        </header>

        <div class="inv-settings__layout">
            <aside class="inv-settings__tree" data-settings-tree>
                <h2 class="inv-settings__section-title">Kayıtlar</h2>
                <ul class="inv-settings__list" data-tree-panel="categories">
                    @foreach ($categories as $category)
                        <li class="inv-settings__node" data-node-id="cat-{{ $category->id }}">{{ $category->name }}</li>
                    @endforeach
                </ul>
                <ul class="inv-settings__list is-hidden" data-tree-panel="variants">
                    @foreach ($variantSets as $variant)
                        <li class="inv-settings__node" data-node-id="var-{{ $variant->id }}">{{ $variant->sku ?? $variant->id }}</li>
                    @endforeach
                </ul>
                <ul class="inv-settings__list is-hidden" data-tree-panel="units">
                    @foreach ($units as $unit)
                        <li class="inv-settings__node" data-node-id="unit-{{ $unit->id }}">{{ $unit->code }} • {{ $unit->name }}</li>
                    @endforeach
                </ul>
            </aside>

            <section class="inv-settings__detail" data-settings-detail>
                <h2 class="inv-settings__section-title">Detay</h2>
                <form class="inv-settings__form">
                    <div class="mb-3">
                        <label class="form-label">Ad</label>
                        <input type="text" class="form-control" data-field="name" placeholder="Seçim yapın">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kod</label>
                        <input type="text" class="form-control" data-field="code">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Not</label>
                        <textarea class="form-control" rows="3" data-field="note"></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" data-action="settings-save">Kaydet</button>
                        <button type="button" class="btn btn-outline-secondary" data-action="settings-reset">Vazgeç</button>
                    </div>
                </form>

                <div class="inv-settings__bulk" data-bulk-actions>
                    <h3 class="inv-settings__section-subtitle">Toplu İşlemler</h3>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-action="bulk-rename">Yeniden Adlandır</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-action="bulk-move">Taşı</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-action="bulk-delete">Sil</button>
                    <p class="inv-settings__alert text-muted">Bu işlemler ilgili ürün kayıtlarını etkileyebilir.</p>
                </div>
            </section>
        </div>
    </section>
@endsection
