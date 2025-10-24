# K4 — Marketing Genişletme Özeti

Bu sürüm, pazarlama modülünü müşteri yönetimi, satış siparişleri, fiyat listesi toplu güncellemeleri ve iade talepleri ile genişletir.

## Müşteriler
- **Tablo**: `customers` — yeni alanlar: `payment_terms_days`, `default_price_list_id`, `credit_limit`, `billing_address`, `shipping_address`, `is_active`.
- **İzinler**: `marketing.customers.view|create|update|delete` (eski `marketing.customer.*` izinleri geriye dönük olarak tanımlı kalır).
- Varsayılan fiyat listesi ve vade gün bilgisi sipariş formuna otomatik taşınır.

## Satış Siparişleri
- **Tablo**: `sales_orders`, satır tablosu `sales_order_lines`.
- **Durumlar**: `draft`, `confirmed`, `fulfilled`, `cancelled`.
- **İzinler**: `marketing.orders.view|create|update|confirm|cancel` (eski `marketing.order.*` anahtarları desteklenmeye devam eder).
- **Varsayılanlar**: Müşteri kartı > Ayarlar Defaults > sistem fallback sıralamasıyla doldurulur.
- **Stok sinyali**: `stock_ledger` toplamı üzerinden `in/low/out` rozeti gösterilir; precision ayarı `Settings.general.decimal_precision` değerini kullanır.

## Fiyat Listesi Toplu Güncelleme
- **Servis**: `App\Modules\Marketing\Domain\PricelistBulkUpdater` filtreden geçen öğeler üzerinde yüzde veya sabit tutarlı artış/azalış uygular.
- **İzin**: `marketing.pricelists.bulk_update` (legacy `marketing.pricelist.bulk_update` anahtarı korunur).
- **Feature flag**: `config('features.marketing.pricelists_bulk_update')` ile kapatılabilir.

## İade Talepleri (RMA)
- **Tablo**: `returns`, satırlar `return_lines`.
- **Durumlar**: `open`, `approved`, `closed`.
- **İzinler**: `marketing.returns.view|create|approve|close` (önceki `marketing.return.*` izinleri geriye dönük olarak listede tutulur).
- Stok veya muhasebe hareketi oluşmaz; ileride lojistikle bağlanacaktır.
- **Feature flag**: `config('features.marketing.returns')`.

## Navigasyon
- Marketing menüsü artık Müşteriler, Siparişler, Fiyat Listeleri ve (flag açıksa) İadeler girişlerini içerir.

## Sıralı Varsayılanlar
1. Müşteri kartı (`default_price_list_id`, `payment_terms_days`).
2. Settings V2 Defaults (`defaults.price_list_id`, `defaults.payment_terms_days`, `defaults.tax_inclusive`).
3. Sistem varsayılanları (TRY, 0 gün, vergi hariç).

## Audit ve Loglar
- Sipariş onay ve iptal işlemleri politika kontrollerinden geçer.
- Toplu güncellemeler tek transaction ile yazılır.
