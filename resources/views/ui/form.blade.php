@extends('layouts.admin')

@section('title', 'UI Gallery')
@section('section', 'UI Gallery')

@push('page-styles')
    @vite('resources/scss/pages/ui-gallery.scss')
@endpush

@push('page-scripts')
    @vite('resources/js/pages/ui-gallery.js')
@endpush

@section('content')
<x-ui-content class="ui-gallery" data-ui="gallery">
    <section class="ui-gallery__section" id="tables">
        <header class="ui-gallery__header">
            <div>
                <h2 class="ui-gallery__title">Tablo &amp; Liste</h2>
                <p class="ui-gallery__description">Sticky kolonlar, yoğunluk kalıcılığı ve durum rozetleri ile veri görünümü.</p>
            </div>
        </header>
        <div class="ui-gallery__table">
            @php
                $inventoryColumns = [
                    ['id' => 'id', 'label' => 'ID', 'type' => 'id', 'pin' => 'left', 'meta' => 'Dar & sabit'],
                    ['id' => 'code', 'label' => 'Kod', 'type' => 'code', 'meta' => 'SKU'],
                    ['id' => 'name', 'label' => 'Tanım', 'type' => 'text', 'meta' => 'Esnek', 'clamp' => 2],
                    ['id' => 'category', 'label' => 'Kategori'],
                    ['id' => 'vendor', 'label' => 'Tedarikçi'],
                    ['id' => 'stock', 'label' => 'Stok', 'type' => 'number', 'numeric' => true, 'unit' => 'Adet', 'totalizable' => true],
                    ['id' => 'reserved', 'label' => 'Rezerve', 'type' => 'number', 'numeric' => true, 'totalizable' => true],
                    ['id' => 'price', 'label' => 'Fiyat', 'type' => 'currency', 'numeric' => true, 'unit' => '₺', 'totalizable' => true],
                    ['id' => 'due', 'label' => 'Vade'],
                    ['id' => 'status', 'label' => 'Durum'],
                ];

                $inventoryRows = [
                    [
                        'id' => 'INV-001',
                        'code' => 'SKU-311',
                        'name' => ['type' => 'clamp', 'value' => 'Metal raf ünitesi', 'lines' => 2],
                        'category' => 'Depo',
                        'vendor' => 'Rafist',
                        'updated' => '12.10.24',
                        'stock' => number_format(48),
                        'reserved' => number_format(5),
                        'price' => '₺' . number_format(349.90, 2),
                        'due' => 'Net 30',
                        'status' => ['type' => 'badge', 'variant' => 'success', 'label' => 'Uygun'],
                    ],
                    [
                        'id' => 'INV-002',
                        'code' => 'SKU-318',
                        'name' => ['type' => 'clamp', 'value' => 'Endüstriyel ışıklandırma kiti — uzun açıklama ile iki satır örneği', 'lines' => 2],
                        'category' => 'Elektrik',
                        'vendor' => 'Luma',
                        'updated' => '09.10.24',
                        'stock' => number_format(12),
                        'reserved' => number_format(8),
                        'price' => '₺' . number_format(1299.50, 2),
                        'due' => 'Net 45',
                        'status' => ['type' => 'badge', 'variant' => 'info', 'label' => 'Takipte'],
                    ],
                    [
                        'id' => 'INV-003',
                        'code' => 'SKU-127',
                        'name' => ['type' => 'clamp', 'value' => 'Soğuk depo sensörü', 'lines' => 2],
                        'category' => 'IoT',
                        'vendor' => 'ThermoSense',
                        'updated' => '05.10.24',
                        'stock' => number_format(0),
                        'reserved' => number_format(2),
                        'price' => '₺' . number_format(799.00, 2),
                        'due' => 'Ödeme bekliyor',
                        'status' => ['type' => 'badge', 'variant' => 'warning', 'label' => 'Stok yok'],
                    ],
                    [
                        'id' => 'INV-004',
                        'code' => 'SKU-442',
                        'name' => ['type' => 'clamp', 'value' => 'Lityum güç modülü', 'lines' => 2],
                        'category' => 'Enerji',
                        'vendor' => 'VoltPro',
                        'updated' => '02.10.24',
                        'stock' => number_format(6),
                        'reserved' => number_format(6),
                        'price' => '₺' . number_format(2299.90, 2),
                        'due' => 'Gecikmiş',
                        'status' => ['type' => 'badge', 'variant' => 'danger', 'label' => 'Vade geçti'],
                    ],
                    [
                        'id' => 'INV-005',
                        'code' => 'SKU-509',
                        'name' => ['type' => 'clamp', 'value' => 'Çok amaçlı taşıma arabası', 'lines' => 2],
                        'category' => 'Lojistik',
                        'vendor' => 'MoveIt',
                        'updated' => '11.10.24',
                        'stock' => number_format(82),
                        'reserved' => number_format(12),
                        'price' => '₺' . number_format(549.00, 2),
                        'due' => 'Net 30',
                        'status' => ['type' => 'badge', 'variant' => 'success', 'label' => 'Stabil'],
                    ],
                ];

                $inventoryTotals = [
                    'id' => 'Toplam',
                    'stock' => number_format(48 + 12 + 0 + 6 + 82),
                    'reserved' => number_format(5 + 8 + 2 + 6 + 12),
                    'price' => '₺' . number_format(349.90 + 1299.50 + 799.00 + 2299.90 + 549.00, 2),
                    'due' => 'Δ +3 gün',
                ];
            @endphp

            <x-ui-table
                id="gallery-table"
                tableId="inventory-main"
                :columns="$inventoryColumns"
                :rows="$inventoryRows"
                :totals="$inventoryTotals"
                :row-actions="[
                    ['label' => 'bi bi-eye', 'action' => 'view'],
                    ['label' => 'Düzenle', 'action' => 'edit'],
                    ['label' => 'Arşivle', 'action' => 'archive']
                ]"
            >
                <x-slot name="headerExtra">
                    <div class="ui-gallery__table-modes" role="group" aria-label="Okunurluk modu">
                        <button type="button" class="ui-gallery__toggle" data-action="table-readability" data-target="#gallery-table" aria-pressed="false">Okunurluk</button>
                        <button type="button" class="ui-gallery__toggle" data-action="table-reset" data-target="#gallery-table">Varsayılan</button>
                    </div>
                </x-slot>
            </x-ui-table>

            <x-ui-card title="Arama Akışı" subtitle="GET + scopeSearch" class="ui-gallery__card">
                <ul class="ui-gallery__list">
                    <li>Form GET ile <code>?q=</code> parametresi gönderir; JS yalnızca tabloyu <abbr title="Asynchronous JavaScript and XML">AJAX</abbr> yerine <code>aria-busy</code> ile işaretler.</li>
                    <li>Controller <code>scopeSearch($q)</code> çağırır, aranabilir alan beyaz listesini (kod, ad, kategori) kullanır ve sonuçları sayfalar.</li>
                    <li>Toplamlar server tarafında hesaplanıp <code>:totals</code> slotuna verilir; sonuç yoksa boş durum + “Filtreleri temizle” bağlantısı döner.</li>
                </ul>
            </x-ui-card>

            <figure class="ui-gallery__diagram" aria-describedby="column-diagram-note">
                <figcaption id="column-diagram-note">Kolon Reçetesi</figcaption>
                <div class="ui-gallery__diagram-grid" role="list">
                    <span role="listitem">ID / Kod<br><small>Dar &amp; sabit, pin opsiyonu</small></span>
                    <span role="listitem">Tanım<br><small>Esnek genişlik, 2 satır clamp</small></span>
                    <span role="listitem">Sayısal<br><small>Sağ hizalı, toplamlanabilir</small></span>
                    <span role="listitem">Aksiyon<br><small>Sağ sabit 64px, pinlenmiş</small></span>
                </div>
            </figure>
        </div>
    </section>





</x-ui-content>
@endsection
