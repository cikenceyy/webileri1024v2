# Finance Module Refresh — Acceptance Summary

## Kalan Rotalar
- `admin.finance.home` → Kontrol Merkezi dashboard (`FinanceHomeController`).
- `admin.finance.collections.index/show/lane` → Tahsilat Konsolu kanban ve slideover özetleri.
- `admin.finance.invoices.*` (+ `print`, `from-order`) → Fatura Stüdyosu akışı.
- `admin.finance.receipts.index/create/store/show` → Minimal tahsilat yönetimi.
- `admin.finance.allocations.store/destroy` → Tahsilat dağıtımı API uçları.
- `admin.finance.cash-panel.*` → Banka & Kasa Paneli kart grid, hareket ekleme ve CSV içe aktarma.
- `admin.finance.transactions.index` → Banka hareketlerinin tablo görünümü.

## Aktif Menü Öğeleri
- Kontrol Merkezi
- Tahsilat Konsolu
- Fatura Stüdyosu
- Banka & Kasa Paneli
- Tahsilatlar

## Asset Girişleri
- JavaScript: `app/Modules/Finance/Resources/js/finance.js`
- SCSS: `app/Modules/Finance/Resources/scss/finance.scss`
- Blade sayfaları `@vite` ile yalnızca Finance girişlerini yüklüyor.

## Temizlik Çıktıları
- `reports.*`, `aging.*` ve `ap-*` ad alanlı rotalar tamamen kaldırıldı.
- Sidebar menüsü raporlama/A/P bağlantılarından arındırıldı ve dört ana ekrana indirildi.
- Tahsilat dağılımı yalnızca API uçları üzerinden yönetiliyor; fazladan sayfa kaldırıldı.

## Ekran Akışı Doğrulamaları
- Kontrol Merkezi: Vade kartları, nakit pozisyonu, son aktiviteler ve N/R/G klavye kısayollu hızlı işlemler tek bakışta aksiyon oluşturuyor.
- Tahsilat Konsolu: Bugün/Bu Hafta/Gecikmiş/Takipte kolonları drag & drop ve slideover özetleriyle kart-first yönetim sağlıyor.
- Fatura Stüdyosu: Siparişten satır çekme, filtre presetleri, kompakt tablo ve hızlı tahsilat slideover’ı aynı ekranda.
- Banka & Kasa Paneli: Kart ızgarası, para birimi filtreleri, hızlı hareket formu ve CSV içe aktarma ile hesap yönetimi hızlandı.

