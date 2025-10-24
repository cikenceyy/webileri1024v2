# K5 — Finance Bağlama

Bu sürümde satış faturaları, tahsilat dağıtımı ve temel cashbook akışı uçtan uca bağlandı. Yeni modül yapısı Settings v2 ile hizalıdır ve tüm finans ekranları `SettingsReader` üzerinden para birimi, vade ve yazdırma şablonu varsayılanlarını kullanır.

## Veri Yapıları
- **invoices / invoice_lines:** satış faturası başlık ve kalemleri. Durumlar: `draft`, `issued`, `partially_paid`, `paid`, `cancelled`. `doc_no` numarası Settings > sequencing bölümünden üretilir, `payment_terms_days` ile due date otomatik hesaplanır.
- **receipts / receipt_applications:** müşteri tahsilatları ve faturalara dağıtım kayıtları. Bir tahsilat birden çok faturaya dağıtılabilir, yeniden dağıtım mevcut kayıtları sıfırlayıp tekrar yazar.
- **cashbook_entries:** kasa/banka giriş-çıkışlarının hafif günlük kaydı. `direction` `in` veya `out` değerlerini alır.

## Servisler
- `InvoiceCalculator` satır bazında vergi ve indirim hesaplayıp toplamları üretir.
- `NumberSequencer` Settings v2’den prefix + padding bilgilerini okuyarak benzersiz numara üretir.
- `ReceiptAllocator` tahsilat uygulamasını tek transaction içerisinde yönetir, eski dağıtımları geri alıp faturaların ödeme durumunu günceller.

## Erişim Yetkileri
```
finance.invoices.view|create|update|issue|cancel|print
finance.receipts.view|create|apply
finance.cashbook.view|create
```
Tüm rotalar `web + tenant + auth + verified` zinciri ardına izin kontrolü içerir.

## Kullanım Akışı
1. **Fatura Oluşturma:** Marketing siparişinden veya manuel olarak draft oluşturulur. Satırlar hesaplanır, Issue işlemi sırasında numara atanır ve due date hesaplanır.
2. **Tahsilat Kaydı:** Tahsilat formu müşteri, tarih, yöntem ve tutarı alır. `Apply` ekranı müşterinin açık faturalarını listeler, dağıtım tutarları burada girilir.
3. **Cashbook (Lite):** Basit gelir/gider kayıtları tutulur. Filtreler yön ve tarih aralıklarını destekler.

## Yazdırma
Fatura yazdırma görünümü Settings > Documents bölümündeki `invoice_print_template` seçimine göre tetiklenir. Varsayılan HTML şablon marka tonlarını kullanır.

## Orchestrasyon Uyumu
Order-to-Cash orkestrasyonu yeni faturalama ve tahsilat servislerini kullanacak şekilde güncellendi; siparişten fatura üretme ve tahsilat adımları Settings varsayılanlarını kullanır.
