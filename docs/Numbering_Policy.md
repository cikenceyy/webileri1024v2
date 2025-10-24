# Numbering Policy

## Mevcut Durum

| Modül | Tablo | Alan | Önceki Durum | Yeni Durum |
| --- | --- | --- | --- | --- |
| Marketing (Satış Siparişleri) | `orders` | `order_no` | Rastgele `ORD-YYYYMM-####` üretiliyordu, çakışma için tekrar deneme yapılıyordu. | `next_number('SO')` ile tenant bazlı sıra, yıllık reset ve otomatik benzersiz indeks. |
| Finance (Satış Faturaları) | `invoices` | `invoice_no` | Rastgele `INV-YYYYMM-####`. | `next_number('INV')` yıllık reset, idempotent üretim. |
| Procurement (Satınalma Siparişleri) | `purchase_orders` | `po_number` | Numara alanı bulunmuyordu, benzersiz kimlik yoktu. | Yeni `po_number` kolonu, `next_number('PO')` ile üretim ve `company_id+po_number` benzersizliği. |
| Production (İş Emirleri) | `work_orders` | `work_order_no` | Rastgele `WO-YYYYMM-####`. | `next_number('WO')` ile aylık reset, dinamik importlarda modül chunk'ı kullanımı. |

## Sequence Tasarımı

* `sequences` tablosu tenant (company_id) ve anahtar (`key`) bazında sayaç değerini saklar.
* Alanlar: `prefix`, `padding`, `reset_period (none|yearly|monthly)`, `last_reset_at`, opsiyonel `scope`.
* Her anahtar için tekil kayıt: `unique(company_id, key, scope)` ve HMR dostu indeksler.
* İsteğe bağlı idempotency: `issued_numbers` tablosu `idempotency_key` ile daha önce verilen numarayı döndürür.

## Varsayılan Anahtarlar

| Anahtar | Prefix | Reset | İlgili Tablo/Alan |
| --- | --- | --- | --- |
| `INV` | `INV` | Yıllık | `invoices.invoice_no` |
| `SO` | `SO` | Yıllık | `orders.order_no` |
| `PO` | `PO` | Yıllık | `purchase_orders.po_number` |
| `WO` | `WO` | Aylık | `work_orders.work_order_no` |

Konfigürasyon `config/numbering.php` dosyasındadır; modüller kendi anahtarlarını buraya ekleyebilir.

## Uygulama İlkeleri

1. `next_number($key, $options, $companyId)` helper'ı bütün servislerde kullanılabilir. Varsayılan olarak `currentCompanyId()` kullanılır.
2. Document create akışlarında numara üretimi aynı DB transaction içerisinde yapılmalıdır; benzersiz indeks çakışırsa işlem geri alınır ve sayaç artışı rollback edilir.
3. Reset politikası:
   * `yearly` → yeni yıla girince sayaç sıfırlanır.
   * `monthly` → yıl+ay kombinasyon değişince sıfırlanır.
   * `none` → sürekli artış.
4. İdempotency kullanmak için `['idempotency_key' => $uuid]` parametresi gönderilmelidir.
5. CLI komutları:
   * `php artisan webileri:sequence:seed` → yerel ortamda varsayılan anahtarları oluşturur (isteğe bağlı `--force`).
   * `php artisan webileri:sequence:audit` → sayaç ile gerçek veriler arasındaki farkı raporlar ve `docs/Numbering_Audit.md` dosyasını günceller.

## Entegrasyon Noktaları

* Marketing siparişleri: `Order::generateOrderNo()` helper üzerinden `SO` sekansını çağırır.
* Finance faturaları: `NumberSequencer::nextInvoiceNumber()` Settings v2 `invoice_prefix`+`padding` kombinasyonunu kullanır; tüm controller ve orkestrasyonlar bu servisi çağırır.
* Procurement siparişleri: `PurchaseOrder::generateNumber()` `PO` sekansını kullanır; controller yeni kayıt oluştururken `po_number` alanını doldurur.
* Production iş emirleri: `WorkOrder::generateNo()` `WO` sekansını kullanır; aylık reset ile modül bazlı iş yükü kontrol edilir.

## Operasyonel Notlar

* Audit komutu `storage/logs/sequence_audit.log` içine ham veriyi de yazar.
* İki isteğin aynı idempotency anahtarı ile gelmesi durumunda aynı numara döner ve sayaç artmaz.
* Seri formatını değiştirmek gerektiğinde `sequences` kaydındaki `prefix/padding/reset_period` alanlarını güncellemek yeterlidir; helper sonraki çağrıda yeni formatı kullanır.
