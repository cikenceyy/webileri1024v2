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
    <section class="ui-gallery__section">
        <header class="ui-gallery__header">
            <div>
                <h1 class="ui-gallery__title">Admin Omurga Vitrini</h1>
                <p class="ui-gallery__description">Sabit krom, tematik varyantlar ve hareket dili tek sayfada doğrulanır.</p>
            </div>
            <div class="ui-gallery__controls">
                <div class="ui-gallery__theme-chipset" aria-label="Aktif tema">
                    <span class="ui-gallery__toggle-label">Tema</span>
                    <span class="ui-gallery__theme-pill">Bluewave</span>
                </div>
                <div class="ui-gallery__toggles" role="group" aria-label="Hareket modu">
                    <span class="ui-gallery__toggle-label">Motion</span>
                    <button type="button" class="ui-gallery__toggle" data-action="motion" data-motion="soft" aria-pressed="false">Soft</button>
                    <button type="button" class="ui-gallery__toggle" data-action="motion" data-motion="reduced" aria-pressed="false">Reduced</button>
                </div>
            </div>
        </header>
        <div class="ui-gallery__grid ui-gallery__grid--intro">
            <x-ui-card title="Header" subtitle="Blur yalnız scroll sonrası" class="ui-gallery__card">
                <ul class="ui-gallery__list">
                    <li>64px sabit yükseklik, scroll &gt; 8px olduğunda blur + shadow tetiklenir.</li>
                    <li>Focus-visible ring ile toggle erişilebilirliği.</li>
                </ul>
            </x-ui-card>
            <x-ui-card
                title="Tema dengesi"
                subtitle="Yüzey parlaklığı × mikro gölge"
                class="ui-gallery__card ui-gallery__card--compare"
                data-ui="theme-compare"
            >
                <p class="ui-gallery__description">Soft Indigo ile Industrial Gray arasında ton/parlaklık dengesi tek panelde kıyaslanır.</p>
                <div class="ui-gallery__theme-panel" role="list">
                    <div class="ui-gallery__theme-option" data-theme="bluewave" data-grade="best" data-note="Bluewave temasında parlak yüzey ile 600/700 tonlu vurgular AA kontrastını korur." role="listitem">
                        <span class="ui-gallery__theme-chip" aria-hidden="true">
                            <span class="ui-gallery__swatch ui-gallery__swatch--surface"></span>
                            <span class="ui-gallery__swatch ui-gallery__swatch--shadow"></span>
                        </span>
                        <div class="ui-gallery__theme-meta">
                            <strong>Bluewave Base</strong>
                            <span>Yüksek parlaklık, derin mavi gölge</span>
                        </div>
                        <span class="ui-gallery__theme-badge">Önerilen</span>
                    </div>
                    <div class="ui-gallery__theme-option" data-theme="bluewave" data-grade="alt" data-note="Mat yüzey ve hafif gölgeler, ikincil panellerde düşük stres sağlar." role="listitem">
                        <span class="ui-gallery__theme-chip" aria-hidden="true">
                            <span class="ui-gallery__swatch ui-gallery__swatch--surface ui-gallery__swatch--muted"></span>
                            <span class="ui-gallery__swatch ui-gallery__swatch--shadow ui-gallery__swatch--muted"></span>
                        </span>
                        <div class="ui-gallery__theme-meta">
                            <strong>Bluewave Soft Focus</strong>
                            <span>Mat yüzey, pastel gölge</span>
                        </div>
                    </div>
                </div>
                <p class="ui-gallery__theme-note" data-ui="theme-compare-note">Bluewave temasında parlak yüzey + yoğun gölgeler AA kontrastını en konforlu seviyede tutar.</p>
            </x-ui-card>
            <x-ui-card title="Sidebar" subtitle="Dar mod A/B" class="ui-gallery__card">
                <p>Sidebar genişliği 280px; dar modda ikon + tooltip veya mikro-chip etiketleri.</p>
                <div class="ui-gallery__sidebar-variants">
                    <span class="ui-gallery__toggle-label">Etiket modu</span>
                    <button type="button" class="ui-gallery__toggle" data-action="sidebar-variant" data-variant="tooltip" aria-pressed="false">Tooltip</button>
                    <button type="button" class="ui-gallery__toggle" data-action="sidebar-variant" data-variant="chip" aria-pressed="false">Chip</button>
                </div>
            </x-ui-card>
            <x-ui-card title="Toast bölgesi" subtitle="En fazla üç öğe" class="ui-gallery__card">
                <p>Toast kuyruğu event bus üzerinden yönetilir, 3 öğe sınırı ile temiz kalır.</p>
                <x-ui-button data-action="toast" data-message="Yeni sipariş oluşturuldu">Örnek toast</x-ui-button>
            </x-ui-card>
            <x-ui-card title="Movement Grammar" subtitle="Her hareket tek sözlükten" class="ui-gallery__card">
                <p class="ui-gallery__description">Drawer, modal, tablo ve toast hareketleri aynı token setini kullanır.</p>
                <dl class="ui-gallery__grammar">
                    <div class="ui-gallery__grammar-item">
                        <dt>Drawer</dt>
                        <dd>translateX 12px · var(--t-med) · ease-soft</dd>
                    </div>
                    <div class="ui-gallery__grammar-item">
                        <dt>Modal</dt>
                        <dd>translateY 8px · var(--t-med) · ease-soft</dd>
                    </div>
                    <div class="ui-gallery__grammar-item">
                        <dt>Tablo yoğunluk</dt>
                        <dd>Opacity fade 120ms · transform yok</dd>
                    </div>
                    <div class="ui-gallery__grammar-item">
                        <dt>Inline edit</dt>
                        <dd>Pulse highlight 120ms · Undo kapsülü 5sn</dd>
                    </div>
                </dl>
                <p class="ui-gallery__description ui-gallery__description--note">Overlay açıkken tablolar ve listeler <code>.is-frozen</code> ile hareketi durdurur.</p>
            </x-ui-card>
        </div>
    </section>

    <section class="ui-gallery__section" id="forms">
        <header class="ui-gallery__header">
            <div>
                <h2 class="ui-gallery__title">Form Elemanları</h2>
                <p class="ui-gallery__description">Normal, disabled, hata ve yardım durumları ile temel alanlar.</p>
            </div>
        </header>
        <div class="ui-gallery__grid ui-gallery__grid--forms">
            <x-ui-card title="Input ailesi" class="ui-gallery__card">
                <x-ui-input label="Şirket" name="company" placeholder="Acme" help="Şirket adı" />
                <x-ui-input label="E-posta" name="email" type="email" error="Geçerli e-posta girin" />
                <x-ui-input label="Sorumlu" name="owner" value="Ayşe Yılmaz" disabled />
                <x-ui-number label="Adet" name="units" help="Stok adedi" />
                <x-ui-date label="Teslimat" name="delivery" />
                <x-ui-search placeholder="SKU ara" />
                <x-ui-textarea label="Notlar" name="notes">Teslimat detayı…</x-ui-textarea>
                <x-ui-switch label="Stok uyarıları" name="alerts" />
                <x-ui-select label="Durum" name="status" :options="['draft' => 'Taslak', 'active' => 'Aktif', 'archived' => 'Arşiv']" />
            </x-ui-card>
            <x-ui-card title="Inline edit" class="ui-gallery__card">
                <x-ui-inline-edit name="project" value="Kış Kampanyası" />
                <x-ui-inline-edit name="owner" placeholder="Sorumlu ekle" />
            </x-ui-card>
        </div>
    </section>

    <section class="ui-gallery__section" id="tables">
        <header class="ui-gallery__header">
            <div>
                <h2 class="ui-gallery__title">Tablo &amp; Liste</h2>
                <p class="ui-gallery__description">Sticky kolonlar, yoğunluk kalıcılığı ve durum rozetleri ile veri görünümü.</p>
            </div>
            <div class="ui-gallery__motion-toggles">
                <div class="ui-gallery__toggles" role="group" aria-label="Drawer hız seçenekleri">
                    <span class="ui-gallery__toggle-label">Drawer</span>
                    <button type="button" class="ui-gallery__toggle" data-action="motion-speed" data-target="#demo-drawer" data-duration="120" aria-pressed="false">120ms</button>
                    <button type="button" class="ui-gallery__toggle" data-action="motion-speed" data-target="#demo-drawer" data-duration="200" data-default="true" aria-pressed="false">200ms</button>
                    <button type="button" class="ui-gallery__toggle" data-action="motion-speed" data-target="#demo-drawer" data-duration="260" aria-pressed="false">260ms</button>
                </div>
                <div class="ui-gallery__toggles" role="group" aria-label="Modal hız seçenekleri">
                    <span class="ui-gallery__toggle-label">Modal</span>
                    <button type="button" class="ui-gallery__toggle" data-action="motion-speed" data-target="#demo-modal" data-duration="120" aria-pressed="false">120ms</button>
                    <button type="button" class="ui-gallery__toggle" data-action="motion-speed" data-target="#demo-modal" data-duration="200" aria-pressed="false">200ms</button>
                    <button type="button" class="ui-gallery__toggle" data-action="motion-speed" data-target="#demo-modal" data-duration="260" data-default="true" aria-pressed="false">260ms</button>
                </div>
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
                    ['id' => 'updated', 'label' => 'Güncelleme'],
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
                    ['label' => 'Görüntüle', 'action' => 'view'],
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

    <section class="ui-gallery__section" id="containers">
        <header class="ui-gallery__header">
            <h2 class="ui-gallery__title">Konteyner &amp; Overlay</h2>
        </header>
        <div class="ui-gallery__grid">
            <x-ui-card title="Kart" subtitle="Radius = 0" class="ui-gallery__card">
                <x-slot name="actions">
                    <x-ui-button size="sm">Aksiyon</x-ui-button>
                </x-slot>
                <x-ui-badge variant="info">Yeni</x-ui-badge>
                <p>8/16/24 ritmi, kart içi boşluk 16px.</p>
            </x-ui-card>
            <x-ui-card title="Drawer" subtitle="Yatay animasyon" class="ui-gallery__card">
                <x-ui-drawer id="demo-drawer" title="Yeni Sipariş" width="lg">
                    <p>Drawer PE: JS yoksa form sayfası olarak açılır; açıkken gövde scroll kilitlenir.</p>
                    <p>Tablo satırı tetiklediğinde state korunur, kapanınca odak aksiyona döner.</p>
                    <x-slot name="footer">
                        <x-ui-button data-action="close" variant="ghost">Kapat</x-ui-button>
                        <x-ui-button>Kaydet</x-ui-button>
                    </x-slot>
                </x-ui-drawer>
                <x-ui-button data-action="open" data-target="#demo-drawer">Drawer Aç</x-ui-button>
            </x-ui-card>
            <x-ui-card title="Modal" subtitle="Dikey animasyon" class="ui-gallery__card">
                <x-ui-modal id="demo-modal" title="Önizleme" size="sm">
                    <p>Overlay portal sistemiyle body altında katmanlanır; blur + tek gölge.</p>
                    <p>ESC kapatır, focus tetikleyiciye döner.</p>
                    <x-slot name="footer">
                        <x-ui-button data-action="close" variant="ghost">Vazgeç</x-ui-button>
                        <x-ui-button>Onayla</x-ui-button>
                    </x-slot>
                </x-ui-modal>
                <x-ui-button data-action="open" data-target="#demo-modal">Modal Aç</x-ui-button>
            </x-ui-card>
            <x-ui-card title="Toast" subtitle="Event bus" class="ui-gallery__card">
                <x-ui-toast title="Stok düşük" message="SKU-131 için stok 5" />
                <x-ui-button data-action="toast" data-message="Yeni toast">Toast Göster</x-ui-button>
            </x-ui-card>
        </div>
    </section>

    <section class="ui-gallery__section" id="navigation">
        <header class="ui-gallery__header">
            <h2 class="ui-gallery__title">Navigasyon &amp; Toolbar</h2>
        </header>
        <div class="ui-gallery__grid">
            <x-ui-card title="Breadcrumbs" class="ui-gallery__card">
                <x-ui-breadcrumbs :items="[
                    ['label' => 'Ana sayfa', 'url' => '#'],
                    ['label' => 'Envanter', 'url' => '#'],
                    ['label' => 'SKU-131']
                ]" />
            </x-ui-card>
            <x-ui-card title="Tabs" class="ui-gallery__card">
                <x-ui-tabs :tabs="[
                    ['id' => 'tab-overview', 'label' => 'Özet'],
                    ['id' => 'tab-activity', 'label' => 'Aktivite'],
                    ['id' => 'tab-files', 'label' => 'Dosyalar']
                ]">
                    <div id="tab-overview-panel" role="tabpanel" aria-labelledby="tab-overview-tab" class="ui-tabs__panel is-active">
                        <p>Özet içerik.</p>
                    </div>
                    <div id="tab-activity-panel" role="tabpanel" aria-labelledby="tab-activity-tab" class="ui-tabs__panel" hidden>
                        <p>Aktivite kronolojisi.</p>
                    </div>
                    <div id="tab-files-panel" role="tabpanel" aria-labelledby="tab-files-tab" class="ui-tabs__panel" hidden>
                        <p>Dosya listesi.</p>
                    </div>
                </x-ui-tabs>
            </x-ui-card>
            <x-ui-card title="Pagination" class="ui-gallery__card">
                <x-ui-pagination :current="2" :total="6" />
            </x-ui-card>
            <x-ui-card title="Toolbar" class="ui-gallery__card">
                <x-ui-toolbar :items="[
                    ['label' => 'Filtre', 'icon' => '⛃', 'action' => 'filter'],
                    ['label' => 'Temizle', 'icon' => '✕', 'action' => 'filter-clear'],
                    ['label' => 'Yoğunluk', 'icon' => '☰', 'action' => 'density']
                ]">
                    <span class="ui-toolbar__badge" data-ui="toolbar-filter-badge" hidden>
                        <span aria-hidden="true">●</span> Aktif filtre
                    </span>
                    <x-ui-button size="sm" variant="ghost">Paylaş</x-ui-button>
                </x-ui-toolbar>
            </x-ui-card>
        </div>
    </section>

    <section class="ui-gallery__section" id="feedback">
        <header class="ui-gallery__header">
            <h2 class="ui-gallery__title">Durum &amp; Geri Bildirim</h2>
        </header>
        <div class="ui-gallery__grid">
            <x-ui-card title="Badges" class="ui-gallery__card">
                <x-ui-badge variant="info">Bilgi</x-ui-badge>
                <x-ui-badge variant="success">Başarılı</x-ui-badge>
                <x-ui-badge variant="warning">Uyarı</x-ui-badge>
            </x-ui-card>
            <x-ui-card title="KPI" class="ui-gallery__card">
                <x-ui-kpi label="Aylık gelir" value="₺280K" delta="%12↑" trend="Yükselen" />
            </x-ui-card>
            <x-ui-card title="Stat" class="ui-gallery__card">
                <x-ui-stat label="Aktif abonelik" value="1.240" description="12 yeni" />
            </x-ui-card>
            <x-ui-card title="Boş durum" class="ui-gallery__card">
                <x-ui-empty />
            </x-ui-card>
            <x-ui-card title="Skeleton" class="ui-gallery__card">
                <x-ui-skeleton :lines="4" />
            </x-ui-card>
            <x-ui-card title="Spinner" class="ui-gallery__card">
                <x-ui-spinner />
            </x-ui-card>
            <x-ui-card title="Confirm" class="ui-gallery__card">
                <x-ui-confirm id="confirm-delete" title="Kaydı sil" message="Seçili kaydı silmek üzeresiniz." type="danger" />
                <x-ui-button data-action="open" data-target="#confirm-delete">Silme onayı</x-ui-button>
            </x-ui-card>
            <x-ui-card title="Algısal hız kiti" class="ui-gallery__card ui-gallery__card--speed">
                <div class="ui-gallery__speed" data-ui="speed-demo">
                    <div class="ui-gallery__speed-track" data-variant="skeleton">
                        <span class="ui-gallery__speed-label">Skeleton → İçerik</span>
                        <div class="ui-gallery__speed-surface" data-state="skeleton"></div>
                    </div>
                    <div class="ui-gallery__speed-track" data-variant="content">
                        <span class="ui-gallery__speed-label">Doğrudan içerik</span>
                        <div class="ui-gallery__speed-surface" data-state="content"></div>
                    </div>
                </div>
                <div class="ui-gallery__speed-controls">
                    <button type="button" class="ui-gallery__toggle" data-action="speed" data-target="skeleton" aria-pressed="true">Kademeli</button>
                    <button type="button" class="ui-gallery__toggle" data-action="speed" data-target="content" aria-pressed="false">Anlık</button>
                </div>
                <p class="ui-gallery__description ui-gallery__description--note">Kademeli geçiş skeleton opaklığını artırırken reduced motion’da ikisi de anlık gösterilir.</p>
            </x-ui-card>
        </div>
    </section>

    <section class="ui-gallery__section" id="a11y">
        <header class="ui-gallery__header">
            <h2 class="ui-gallery__title">Erişilebilirlik Turu</h2>
            <p class="ui-gallery__description">Klavye akışı, focus geri dönüşü ve ESC davranışı canlı test edilir.</p>
        </header>
        <div class="ui-gallery__grid ui-gallery__grid--a11y">
            <x-ui-card title="Klavye kısayolları" class="ui-gallery__card">
                <ul class="ui-gallery__list">
                    <li><strong>Tab / Shift+Tab:</strong> Krom ve içerik içinde doğal odak sırası.</li>
                    <li><strong>Enter / Space:</strong> Data-action butonlarını tetikler.</li>
                    <li><strong>ESC:</strong> Açık modal veya drawer kapanır, tetikleyen odağa döner.</li>
                </ul>
            </x-ui-card>
            <x-ui-card title="Focus ring" class="ui-gallery__card">
                <p>:focus-visible mixini tüm etkileşimli öğelerde sınamak için aşağıdaki butonu kullanın.</p>
                <x-ui-button class="ui-gallery__focus-demo">Focus turunu başlat</x-ui-button>
            </x-ui-card>
        </div>
    </section>
</x-ui-content>
@endsection
