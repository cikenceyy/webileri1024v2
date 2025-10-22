# Inventory Menü & Route Manifesti

Tüm route isimleri `admin.inventory.*` ön eki ile tanımlanır. Aşağıdaki liste, bilgi mimarisi ve menü hiyerarşisini özetler.

- **Kontrol Kulesi**
  - Route: `admin.inventory.home`
  - Menü: Dashboard > Inventory
- **Stok İşlem Konsolu**
  - Route: `admin.inventory.stock.console`
  - Alt aksiyonlar: `mode=in|out|transfer|adjust`
- **Ürünler**
  - Liste: `admin.inventory.products.index`
  - Detay: `admin.inventory.products.show`
  - Üründe Kullanılan Malzemeler: `admin.inventory.products.components`
- **Depolar**
  - Liste: `admin.inventory.warehouses.index`
  - Detay: `admin.inventory.warehouses.show`
- **Fiyat Listeleri**
  - Liste: `admin.inventory.pricelists.index`
  - Detay: `admin.inventory.pricelists.show`
- **Ürün Reçeteleri (BOM)**
  - Liste: `admin.inventory.bom.index`
  - Detay: `admin.inventory.bom.show`
- **Inventory Ayarları**
  - Route: `admin.inventory.settings.index`
  - Sekmeler: `categories | variants | units`

Yan menü önerisi:
1. Kontrol Kulesi
2. Stok Konsolu
3. Ürünler
4. Depolar
5. Fiyat Listeleri
6. Ürün Reçeteleri
7. Kullanılan Malzemeler
8. Ayarlar
