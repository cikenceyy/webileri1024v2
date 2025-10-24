# K9 — Konsol Akışları Genel Bakış

Bu belge, K9 adımında eklenen yedi operasyon konsolunun kapsamını, izin gereksinimlerini ve kısayollarını özetler. Konsollar `app/Consoles` modülünde yer alır ve `/admin/consoles/*` rotaları üzerinden erişilir.

## Konsol Listesi ve Akışları

| Konsol | Rota | Gereken İzinler | Adımlar | Varsayılan Kısayol |
| --- | --- | --- | --- | --- |
| Order to Cash | `admin.consoles.o2c.index` | `marketing.orders.view`, `logistics.shipments.view`, `finance.invoices.view`, `finance.receipts.view` | Orders → Shipments → Invoices → Receipts | <kbd>Enter</kbd> → Ship & Print |
| Procure to Pay | `admin.consoles.p2p.index` | `procurement.view`, `logistics.receipts.view`, `finance.invoices.view` | Purchase Orders → GRNs | <kbd>Enter</kbd> → Receive |
| Make to Order | `admin.consoles.mto.index` | `production.workorders.view` | Candidates → Work Orders | <kbd>Enter</kbd> → Issue |
| Replenish | `admin.consoles.replenish.index` | `inventory.transfers.view` | Low Stock (tek liste) | <kbd>Enter</kbd> → Transfer |
| Returns | `admin.consoles.returns.index` | `marketing.returns.view` | RMA Kayıtları | <kbd>Enter</kbd> → Onayla |
| Quality | `admin.consoles.quality.index` | `logistics.shipments.view` veya `logistics.receipts.view` | Incoming Checks → Outgoing Checks | <kbd>Enter</kbd> → Başarılı |
| Closeout | `admin.consoles.closeout.index` | `finance.invoices.print` veya `finance.receipts.view` | Shipments → Invoices → Receipts → Goods Receipts | <kbd>P</kbd> → Toplu Yazdır |

Her konsolda aşağıdaki ortak klavye kısayolları desteklenir:

- <kbd>/</kbd>: Arama alanına odaklanır.
- <kbd>A</kbd>: Aktif listedeki tüm kayıtları seçer.
- <kbd>Enter</kbd>: Varsayılan aksiyonu tetikler (tablo üstünde belirtilmiştir).
- <kbd>P</kbd>: Yazdırma aksiyonunu tetikler (tanımlı ise).

## Seçim ve Aksiyon Mekaniği

Tüm konsollar, `data-console-root` kök bileşeni üzerinde çalışan ortak JavaScript ile desteklenir (`app/Consoles/Resources/views/admin/partials/script.blade.php`).

- Seçim verileri `ids[]` veya `selection[n][field]` formatında forma gizli input olarak yazılır.
- Replenish konsolu `lines[n][product_id]` & `qty` alanlarını üretir.
- Quality ve Closeout konsolları `selection.*` alanlarını kullanarak çok alanlı seçim gönderir.
- `data-default-action` ve `data-print-action` nitelikleri klavye kısayollarını belirler.

## Özellik Bayrakları

`config/features.php` dosyasında aşağıdaki bayraklar sağlandı:

```php
'logistics' => [
    'quality_blocking' => false, // Kalite başarısızlığı sevkiyatı bloklar
],
'consoles' => [
    'o2c' => true,
    'p2p' => true,
    'mto' => true,
    'replenish' => true,
    'returns' => true,
    'quality' => true,
    'closeout' => true,
],
```

Bayraklar UI görünürlüğünü (navigasyon) ve belirli korumaları kontrol eder. Örneğin `features.logistics.quality_blocking` etkinleştirildiğinde, kalite kontrolünde başarısız olmuş sevkiyatlar Order to Cash konsolundan gönderilemez.

## Navigasyon

`App\Core\Views\AdminSidebar` sınıfı, izin/gate kontrolleri ve özellik bayraklarına göre “Konsollar” bölümünü oluşturur. Her konsol bağlantısı yalnızca ilgili `view*Console` gate’i izin verdiğinde gösterilir.

## Kalite Kayıtları

- Kalite sonuçları `quality_checks` tablosuna yazılır (`database/migrations/2025_05_15_000000_create_quality_checks_table.php`).
- Quality konsolu çoklu seçim ile aynı sonucu birden fazla kayıt için kaydedebilir.
- Not alanı tüm seçilen kayıtlar için aynı açıklamayı kullanır.

## Closeout Yazdırma

Closeout konsolundaki “Seçili Belgeleri Yazdır” aksiyonu, seçilen belgeler için çıktı rotalarını hesaplar ve `consoles::admin.closeout.print` görünümünde bağlantı listesi döndürür. Yazdırma bağlantıları yeni sekmede açılır.

## Testler

Konsol servislerinin kritik yolları, `tests/Feature/Consoles/*` altında yazılan feature testleri ile kapsam altına alınmıştır. Özellikle kalite kayıtları ve closeout yazdırma servisleri için doğrulama yapılır.
