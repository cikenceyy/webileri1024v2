# K6 — Production Merkezi

## Genel Bakış
- BOM başlığı artık çıktı ürünü, sürüm ve varsayılan çıktı miktarı ile birlikte kalemleri tutar.
- İş emri yaşam döngüsü: taslak → serbest → üretimde → tamamlandı → kapalı/iptal.
- Issue işlemi BOM gereksinimlerine göre stok defterine `wo_issue` hareketi yazar.
- Complete işlemi üretimi stok defterine `wo_receipt` olarak kaydeder.

## Tablolar
- `boms`, `bom_items`
- `work_orders`, `work_order_issues`, `work_order_receipts`, `work_order_operations`
- `stock_ledger` sebep alanı `wo_issue` ve `wo_receipt` ile genişletildi.

## İzinler
- `production.boms.{view,create,update,delete}`
- `production.workorders.{view,create,update,release,start,issue,complete,close,cancel}`

## Akışlar
1. **BOM Yönetimi**: Ürün seç, miktarları tanımla, istenirse kopyala.
2. **İş Emri**: Ayar sekansına göre numaralanır. Serbest bırakma ve üretime alma aksiyonları statü değiştirir.
3. **Issue**: Gereken malzeme miktarı önerilir, kullanıcı depo/bin seçer; ledger çıkışı oluşur.
4. **Complete**: Üretilen miktar depo/bin seçilerek stok girişine dönüşür; iş emri tamamlanır ve kapatılabilir.

## Varsayılanlar
- `defaults.production_issue_warehouse_id`
- `defaults.production_receipt_warehouse_id`
- `sequencing.work_order_prefix`

Bu rehber K6 kapsamında yapılan temel üretim merkezleştirme değişikliklerinin kısa özetidir.
