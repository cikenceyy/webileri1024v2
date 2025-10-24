# K3 — Inventory Güçlendirme Özeti

Bu sürümle birlikte envanter modülünde depo/raf yönetimi, stok transferi, stok sayımı ve kategori altyapısı yeniden düzenlenmiştir.

## Veri Modelleri
- `warehouses` tablosuna `is_active` alanı eklendi.
- Yeni tablolar: `warehouse_bins`, `stock_transfers`, `stock_transfer_lines`, `stock_counts`, `stock_count_lines`, `stock_ledger`, `variant_attributes`, `variant_attribute_values`, `product_variant_values`.
- `product_categories` tablosu slug + aktif durum alanlarıyla genişletildi.

## Başlıca Akışlar
- **Depo Konsolu**: Depo ve raf bazında stok özetini gösterir, raf CRUD işlemleri desteklenir.
- **Stok Transferi**: Taslak → Gönderildi yaşam döngüsü, onayda stok defterine giriş/çıkış kayıtları üretir.
- **Stok Sayımı**: Taslak → Counted → Reconciled süreçleri, farklar `stock_ledger` tablosuna yazılır.
- **Kategori Yönetimi**: Hiyerarşik yapı, slug benzersizliği ve aktif/pasif işaretleri içerir.

## İzinler
- Yeni anahtarlar: `inventory.transfer.*`, `inventory.count.*`, `inventory.bin.*`, `inventory.variant_attribute.*`, `inventory.variant_value.*`.

## Navigasyon
- Envanter menüsünde Depolar, Transferler, Sayım ve Kategoriler başlıkları yer aldı.

## Varsayılanlar
- Transfer ve sayım formları SettingsReader üzerinden varsayılan depo bilgilerini ve döküman numaralarını önerir.

## Notlar
- Stok defteri `stock_ledger` tablosu üzerinden tutulur; ileri tarihli audit/görselleştirmeler için temel sağlar.
