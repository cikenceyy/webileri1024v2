# Blade Inventory

Bu rapor mevcut Blade layout, partial ve bileşenlerini inceleyerek yeni `resources/` organizasyonuna geçiş için öneriler sunar.

## Layouts
| Layout | Yol | Tanım | Sections / Yields | Stacks | @vite Kullanımı | Öneri |
| --- | --- | --- | --- | --- | --- | --- |
| `layouts/admin.blade.php` | `resources/views/layouts/admin.blade.php` | Yönetim kabuğu; Today Board + konsolları taşıyor. | `title`, `module`, `section`, `content` | `page-styles`, `page-scripts` | `@vite(['resources/scss/app.scss','resources/js/app.js'])` | Layout Core katmana taşınıp `resources/layouts/admin.blade.php` → `resources/layouts/admin/app.blade.php` şeklinde modülerleştirilmeli; module slug attribute otomatik keşifle beslenecek.

> Layout düzeyi tek dosyada toplanmış durumda; Bootstrap 5 + UI token'ları bu layout üzerinden yükleniyor. Çoklu layout desteği (ör: Public, Console) için parçalanması gerekiyor.

## Partials
| Partial | Yol | Kullanım | Öne Çıkan Bileşenler | Öneri |
| --- | --- | --- | --- | --- |
| Sidebar | `resources/views/partials/sidebar.blade.php` | Admin shell içinde navigation. | statik link listeleri | `resources/partials/layout/sidebar.blade.php` altına taşınarak module discovery (config tabanlı) uygulanmalı.
| Header | `resources/views/partials/header.blade.php` | Üst menü + toolbar. | `<x-ui.toolbar>`, toggle butonları | Header aksiyonları Consoles akışlarına göre dinamikleştirilmeli; toolbar item'ları JSON config ile beslenecek.
| Toast Region | `resources/views/partials/toast.blade.php` | Küresel toast container. | `<x-ui.toast>` | `resources/partials/ui/toast-region.blade.php` altına taşınıp Vite entry ile lazy-load edilecek.

## Blade Componentleri
`resources/views/components` altında 29 adet `ui-*` bileşeni bulunuyor; hepsi `<x-ui.*>` notasyonu ile çağrılıyor. En sık kullanılanlar `ui-card`, `ui-button`, `ui-table`, `ui-modal`. `rg` taraması UI galerisi (`resources/views/ui/index.blade.php`) içinde yoğun kullanım gösteriyor.

| Bileşen | Yol | Amacı | Öneri |
| --- | --- | --- | --- |
| `<x-ui.card>` | `resources/views/components/ui-card.blade.php` | Kart düzeni | Modüller arası paylaşım için `resources/components/ui/card.blade.php` yolu standardize edilmeli; props tipi PHP attribute ile belgelenmeli.
| `<x-ui.table>` | `resources/views/components/ui-table.blade.php` | Responsive tablo ve aksiyon menüsü | Scroll gölgesi ve density kontrolleri JS modülüne bağımlı; module bazlı Vite entry auto-discovery ile table script'i yalnız kullanılan sayfalara dahil edilmeli.
| `<x-ui.modal>` | `resources/views/components/ui-modal.blade.php` | Modal kabuğu | Data attributes design token'ları ile hizalanacak; form slot'u typed component'e çevrilecek.
| `<x-ui.toolbar>` | `resources/views/components/ui-toolbar.blade.php` | Toolbar buton kümeleri | `resources/js/components/toolbar.js` ile eşleşiyor; toolbar item konfigürasyonu modül config dosyalarına taşınmalı.

> Mevcut component klasörü doğrudan root altında. Yeni yapı: `resources/views/components/ui/*` → modül bazlı alt klasörler (`resources/modules/<module>/views/components`) ile tamamlanacak; çekirdek UI bileşenleri `resources/components/ui` altında kalacak.

## Sayfalar ve Görünümler
- Çekirdek UI Galerisi: `resources/views/ui/index.blade.php` ve `resources/views/ui/form.blade.php` tasarım sistemi örnekleri sunuyor. Bu dosyalar yeni `resources/pages/ui` klasörüne taşınmalı.
- Konsol görünümleri: `resources/views/core/consoles/*.blade.php` ve `resources/views/core/boards/today.blade.php` Today Board ve konsol prototiplerini içeriyor; yeni `consoles/` dizini altında modülerleştirilecek.
- Modül spesifik görünümler hâlihazırda ilgili modüllerin `Resources/views` klasörlerinde. Yeni tasarımda bu klasörler `Resources/views/{layouts,partials,components,pages}` olarak yeniden organize edilmeli.

## Bileşen Kullanım İstatistikleri
- `<x-ui.*>` notasyonu tüm UI bileşenlerinde tutarlı; `resources/views/ui/index.blade.php` dosyasında 60+ çağrı var.
- `@stack('page-scripts')` ve `@stack('page-styles')` layout üzerinden expose ediliyor; modül view'lerinde `@push` kullanımları standardize edilmeli.
- Blade componentleri JS tarafında `resources/js/components/*.js` modülleriyle eşleşiyor; tasarım sistemi belgeleri `docs/ui-style-behavior-guide.md` ile uyumlu.

## Önerilen Yeni Yapı
```
resources/
  layouts/
    admin.blade.php
    console.blade.php
  partials/
    layout/
      header.blade.php
      sidebar.blade.php
    ui/
      toast-region.blade.php
  components/
    ui/
      button.blade.php
      table.blade.php
  pages/
    ui/
      index.blade.php
      form.blade.php
    consoles/
      today-board.blade.php
```
- Modül bazlı görünümler: `app/Modules/<Module>/Resources/views/{layouts,partials,components,pages}` hiyerarşisi ile düzenlenecek.
- Design tokens (`resources/scss/tokens`) Blade componentleriyle eşlenerek CSS değişkenleri layout içinde tek noktadan servis edilecek.

