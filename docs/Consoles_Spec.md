# Consoles & Orchestrations Envanteri

Bu doküman, O2C/P2P/MTO konsollarının gerektirdiği servis ve veri kaynaklarının mevcut durumunu özetler.

## Modül Bazında Kullanılabilir Servisler

### Marketing (CRM/Sales → Marketing)
- `App/Modules/Marketing/Domain/Models/Order.php`: sipariş başlığı, durum, müşteri ilişkileri ve `generateOrderNo` yardımcıları sağlar.
- `App/Modules/Marketing/Domain/Models/OrderLine.php`: sipariş satırları ve ürün/variant ilişkileriyle stok entegrasyonuna hazırdır.
- `App/Modules/Marketing/Domain/Models/Customer.php` ve `CustomerContact.php`: müşteri/iletişim bilgileri; tahsilat ve kredi kontrolleri için kullanılır.
- `App/Modules/Marketing/Policies/OrderPolicy.php`: `viewAny`, `approve` gibi işlemleri şirket ve izin bazlı olarak kısıtlar.

### Inventory
- `App/Modules/Inventory/Domain/Services/StockService.php`: rezervasyon, çıkış, giriş ve transfer işlemlerini transaction + doğrulamalar ile yönetir.
- `App/Modules/Inventory/Domain/Models/Warehouse.php`: default ambar seçimi için `is_default` alanı içerir.

### Logistics
- `App/Modules/Logistics/Domain/Models/Shipment.php`: sevkiyat başlık bilgileri, durum, tarih alanları.
- `App/Modules/Logistics/Domain/Services/ShipmentService.php`: picking/packing/ship/deliver adımlarını doğrulama ve stok düşümüyle gerçekleştirir.

### Finance
- `App/Modules/Finance/Domain/Services/BillingService.php`: siparişi faturaya dönüştürüp satır ve toplamları hesaplar.
- `App/Modules/Finance/Domain/Models/Invoice.php` ve `Receipt.php`: AR tarafında bakiye takibi ve tahsilatların kayıt altına alınması.
- `App/Modules/Finance/Domain/Models/ApInvoice.php` ve `ApPayment.php`: AP faturaları ve ödemeleri için yapılandırılmış modeller.

### Procurement
- `App/Modules/Procurement/Domain/Models/PurchaseOrder.php`: PO statüleri, onay tarihleri.
- `App/Modules/Procurement/Domain/Models/Grn.php` ve `GrnLine.php`: mal kabul kayıtları ve satır bazlı ürün/qty bilgileri.

### Production
- `App/Modules/Production/Domain/Services/WoService.php`: sipariş satırlarından iş emri önerisi ve kapama işlemleri.
- `App/Modules/Production/Domain/Models/WorkOrder.php`, `WoMaterialIssue.php`, `WoReceipt.php`: üretim sürecindeki temel varlıklar.

## Orkestrasyon Gereksinim Haritası

| Akış | Adım | Kaynak Servis/Model | Not |
| --- | --- | --- | --- |
| O2C | `so.confirm` | Marketing Order + Inventory StockService | Sipariş onayı sonrası stok rezervasyonu yapılır.
| O2C | `ship.dispatch` | Logistics ShipmentService | Sevkiyat `ship()` ile stok düşer, durum güncellenir.
| O2C | `ar.invoice.post` | Finance BillingService / Invoice | Siparişten fatura üretimi ve bakiye güncellemesi.
| O2C | `ar.payment.register` | Finance Receipt + Allocation | Tahsilat kaydı, bakiye sıfırlama.
| P2P | `po.approve` | Procurement PurchaseOrder | Onay tarihi ve statü güncellemesi.
| P2P | `grn.receive` | Procurement Grn + Inventory StockService | Mal kabul kaydı ve stok girişi.
| P2P | `ap.invoice.post` | Finance ApInvoice + ApInvoiceLine | PO satırlarından AP faturası üretimi.
| P2P | `ap.payment.register` | Finance ApPayment | Ödeme kaydı ve bakiye düşümü.
| MTO | `wo.release` | Production WoService | Sipariş satırından iş emri oluşturma/serbest bırakma.
| MTO | `wo.issue.materials` | Production WoMaterialIssue + Inventory StockService | Malzeme çıkışı ve iş emrine bağlama.
| MTO | `wo.finish` | Production WoService | İş emrini kapatma.
| MTO | `inv.receive.finished` | Production WoReceipt + Inventory StockService | Üretilen ürünün depoya alınması.

## Konsol UI Gözlemleri

- Bootstrap 5 tabanlı kart ve tablo bileşenleri `resources/views/components/ui-*.blade.php` düz yapısındaki `<x-ui-*>` bileşenleri ile tutarlı kullanılabilir.
- Layout `resources/views/layouts/admin.blade.php` data attribute’ları (`data-module`, `data-page`) ile Vite dinamik import akışına hazır.
- Konsollar için yeni Blade sayfaları (`resources/views/consoles/*.blade.php`) filtre formu + KPI kartları + aksiyon tablosu şablonunu paylaşır.

## Açık Noktalar & Riskler

- Malzeme çıkarma (`wo.issue.materials`) adımı, frontend’den materyal listesi gelmediğinde yalnızca statüyü güncelliyor; detaylı reçete entegrasyonu sonraki iterasyonda ele alınmalı.
- `StockService::receive` çağrıları, şirket için default ambar bulunmadığında sessizce atlanıyor; veri kalitesini korumak adına ambar zorunluluğu ileride değerlendirilmeli.
- Konsolların aksiyon butonları şimdilik tek tıkla POST ediyor; step bazlı detay formları (modal) ve idempotency key üretimi UI tarafında planlanmalı.
