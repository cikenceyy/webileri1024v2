# K7 — Logistics Modülü Genel Bakış

K7 ile birlikte lojistik süreçleri için iki ana akış teslim edildi:

- **Sevkiyatlar:** Satış siparişlerine veya bağımsız kayıtlara bağlanabilen, taslaktan sevk edilmeye kadar giden pick → pack → ship süreci.
- **Mal Kabul (GRN):** Satın alma siparişlerinden veya bağımsız girişlerden gelen teslimatların taslaktan uzlaşmaya kadar yönetildiği akış.

## Veri Yapıları

| Tablo | Açıklama |
| --- | --- |
| `shipments` | Sevkiyat başlığı. Durumlar: `draft`, `picking`, `packed`, `shipped`, `closed`, `cancelled`. |
| `shipment_lines` | Satır bazında ürün, miktar, depo/bin ve pick/pack/shipped miktarları. |
| `goods_receipts` | Mal kabul başlığı. Durumlar: `draft`, `received`, `reconciled`, `closed`, `cancelled`. |
| `goods_receipt_lines` | Ürün, varyant, beklenen/alınan miktarlar ve varyans gerekçesi. |
| `variance_reasons` | Opsiyonel varyans kodları (feature flag ile yönetilebilir). |

Her kayıt `company_id` ile tenant scoped tutulur. Stok hareketleri `stock_ledger` tablosuna `reason=shipment` veya `reason=grn` olarak işlenir.

## Durum Geçişleri

### Sevkiyat

1. **Draft → Picking:** Kullanıcı `Toplamayı Başlat` ile statüyü `picking` yapar veya toplama formu kaydedildiğinde otomatik geçer.
2. **Picking:** Satır bazında depo/bin ve `picked_qty` değerleri girilir.
3. **Packed:** Paket bilgileri ve `packed_qty` değerleri kaydedilir.
4. **Shipped:** `ShipmentShipper` servisi paketlenen miktarları sevk eder, stok defterine çıkış yazar ve `ShipmentShipped` olayı yayınlanır.
5. **Closed/Cancelled:** Sevk edilen kayıtlar kapatılabilir, sevk edilmemiş kayıtlar iptal edilebilir.

### Mal Kabul

1. **Draft → Received:** Kabul formu ile `qty_received` bilgisi girilir, stok defterine giriş yazılır ve `GoodsReceiptPosted` olayı yayınlanır.
2. **Reconciled:** Varyans gerekçeleri işlenir. Varyans ≠ 0 ise gerekçe zorunludur.
3. **Closed/Cancelled:** Uzlaşılan kayıtlar kapatılabilir, tamamlanmamışlar iptal edilebilir.

## Varsayılanlar ve Numara Üretimi

- `LogisticsSequencer` hem sevkiyat (`shipment_prefix`) hem de GRN (`grn_prefix`) numaralarını Settings v2 üzerinden üretir.
- `Settings.defaults` altına `shipment_warehouse_id` ve `receipt_warehouse_id` alanları eklendi. Formlar bu değerlerle ön-dolu gelir.
- Yazdırma şablonları `documents.shipment_note_template` ve `documents.grn_note_template` alanları ile seçilebilir. Şablon adı geçerli bir view ise kullanılır, aksi halde varsayılan HTML çıktı render edilir.

## İzinler

Yeni izin anahtarları:

- `logistics.shipments.*` → `view`, `create`, `update`, `pick`, `pack`, `ship`, `close`, `cancel`, `print`
- `logistics.receipts.*` → `view`, `create`, `update`, `receive`, `reconcile`, `close`, `cancel`, `print`

Yetki kontrolleri `ShipmentPolicy` ve `ReceiptPolicy` üzerinden yapılır.

## Eventler

- `ShipmentShipped` sevk işlemi tamamlandığında tetiklenir.
- `GoodsReceiptPosted` mal kabulü stok defterine yazıldığında tetiklenir.

Bu olaylar Order-to-Cash ve Procure-to-Pay orkestrasyonları tarafından dinlenmeye hazırdır.
