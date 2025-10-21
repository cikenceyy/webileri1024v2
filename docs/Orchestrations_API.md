# Orchestrations API Rehberi

Bu doküman, Core orchestrasyon sözleşmesini ve O2C/P2P/MTO akışlarının beklediği payload formatlarını açıklar.

## Sözleşme

`App\Core\Orchestrations\Contracts\OrchestrationContract` üç temel yöntemi tanımlar:

- `preview(array $filters): array` – dashboard için KPI + kuyruk verisi üretir. Her akış kendi DTO'sunu (`O2CState`, `P2PState`, `MTOState`) array’e dönüştürerek döndürür.
- `executeStep(string $step, array $payload, ?string $idempotencyKey = null): StepResult` – belirli bir adımı çalıştırır ve `StepResult` döndürür. `success`, `message`, `data`, `errors`, `nextStep` alanlarını taşır.
- `rollbackStep(string $step, array $payload): StepResult` – isteğe bağlı geri alma noktaları için ayrılmıştır (şu an bilgi amaçlı failure döndürür).

`StepResult` static yardımcıları:

```php
StepResult::success(string $message, array $data = [], ?string $nextStep = null)
StepResult::failure(string $message, array $errors = [])
```

## Akış Bazında Step & Payload Haritası

### Order-to-Cash (`OrderToCashOrchestration`)

| Step | Gerekli Alanlar | Açıklama |
| --- | --- | --- |
| `so.confirm` | `order_id` | Siparişi `confirmed` durumuna geçirir, varsa stok rezervasyonu açar. |
| `inv.allocate` | `order_id` | `StockService::reserveForOrder` ile rezervasyon tekrar çalıştırılır. |
| `ship.dispatch` | `shipment_id` | `ShipmentService::ship` çağrılır, aksi halde status=`shipped`. |
| `ar.invoice.post` | `order_id` | `BillingService::fromOrder` veya fallback ile fatura üretir. |
| `ar.payment.register` | `invoice_id`, `amount?`, `receipt_date?`, `bank_account_id?`, `notes?` | Tahsilat (`Receipt` + `Allocation`) kaydı yapar, bakiye güncellenir. |

### Procure-to-Pay (`ProcureToPayOrchestration`)

| Step | Gerekli Alanlar | Açıklama |
| --- | --- | --- |
| `po.approve` | `purchase_order_id` | PO statüsünü `approved` olarak işaretler. |
| `grn.receive` | `grn_id` | GRN `received` yapılır, default ambara stok girişi yapılır. |
| `ap.invoice.post` | `purchase_order_id`, `invoice_date?`, `due_date?`, `currency?`, `notes?` | PO satırlarından AP faturası üretir. |
| `ap.payment.register` | `ap_invoice_id`, `amount?`, `paid_at?`, `method?`, `reference?`, `notes?` | AP ödemesi kaydedilir ve bakiye güncellenir. |

### Make-to-Order (`MakeToOrderOrchestration`)

| Step | Gerekli Alanlar | Açıklama |
| --- | --- | --- |
| `wo.release` | `order_id?`, `work_order_id?` | Siparişten iş emri üretir veya mevcut emri `released` yapar. |
| `wo.issue.materials` | `work_order_id`, `materials[]?` (product_id, variant_id?, qty, unit?, notes?) | Malzeme çıkışı yapar, `WoMaterialIssue` oluşturur, iş emri `in_progress`. |
| `wo.finish` | `work_order_id` | İş emrini `done` durumuna alır (veya `WoService::close`). |
| `inv.receive.finished` | `work_order_id`, `qty?`, `notes?` | Ürün depoya alınır (`WoReceipt` + stok girişi). |

## Yetkilendirme & İzinler

- Controller katmanında `viewAny` policy çağrıları, listelemeye erişimi kısıtlar.
- Her orchestrasyon içinde `STEP_PERMISSION_MAP` ilgili Spatie permission anahtarını kontrol eder; kullanıcı yetkili değilse `StepResult::failure` döner.

## Hata Yönetimi

- Domain doğrulama hataları `ValidationException` yakalanarak kullanıcıya özet hata listesi gönderilir.
- Beklenmeyen hatalar loglanır (`Log::error`) ve kullanıcıya genel bir uyarı mesajı sunulur.
- `nextStep` alanı, UI tarafında sonraki adımı vurgulamak için kullanılabilir (ör. tahsilat sonrası null).

## İdempotency

- `execute` endpointleri `X-Idempotency-Key` header’ını orchestration’a aktarır. Şu an key yalnız iletiliyor, ileride step bazında cache/lock mekanizması eklenebilir.
