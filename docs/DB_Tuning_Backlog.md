# DB Tuning Backlog

| Tablo | Öneri | Gerekçe |
| --- | --- | --- |
| marketing_orders | `index(company_id, status)` | Konsol & orchestrations siparişleri durum + tenant ile filtreliyor. |
| marketing_order_lines | `index(company_id, order_id)` | Order-to-cash adımlarında satır bazlı sorgular mevcut. |
| finance_invoices | `unique(company_id, number)` + `index(company_id, status, due_date)` | NextNumber entegrasyonu + tahsilat ekranları vade/durum filtreliyor. |
| finance_receipts | `index(company_id, status, received_at)` | Payment register raporları tarih aralığı ile çalışıyor. |
| procurement_purchase_orders | `index(company_id, status)` | P2P konsolu bekleyen siparişleri statüye göre listeliyor. |
| logistics_shipments | `index(company_id, status, planned_dispatch_at)` | O2C sevkiyat akışı planlanan sevkiyat tarihine göre filtreliyor. |
| production_work_orders | `index(company_id, status)` | MTO ekranında iş emirleri statü bazlı. |
| inventory_stock_movements | `index(company_id, type, occurred_at)` | Envanter hareket raporu tip ve tarihe göre filtreleniyor. |

> Not: İndeksler uygulanmadan önce mevcut planları `EXPLAIN` ile doğrulayın; composite indekslerin sırası sorgu planına göre revize edilebilir.
