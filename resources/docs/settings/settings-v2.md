# Settings v2

`SettingsDTO` tekil kaynak olup para birimi, vergi, numaralandırma, varsayılanlar, belge şablonları ve genel bilgiler için alt bölümler içerir.

## DTO Yapısı

```php
[
    'money' => [
        'base_currency' => 'USD',       // ISO 4217
        'allowed_currencies' => ['USD', 'EUR'],
    ],
    'tax' => [
        'default_vat_rate' => 18.0,     // 0-50 arası
        'withholding_enabled' => false,
    ],
    'sequencing' => [
        'invoice_prefix' => 'INV',      // A-Z0-9 ve -_/ karakterleri
        'order_prefix' => 'ORD',
        'shipment_prefix' => 'SHP',
        'grn_prefix' => 'GRN',
        'work_order_prefix' => 'WO',
        'padding' => 6,                 // 3-8 arası
        'reset_policy' => 'yearly',     // yearly | never
    ],
    'defaults' => [
        'payment_terms_days' => 30,     // 0-180 arası
        'warehouse_id' => null,
        'price_list_id' => null,
        'tax_inclusive' => false,
        'production_issue_warehouse_id' => null,
        'production_receipt_warehouse_id' => null,
        'shipment_warehouse_id' => null,
        'receipt_warehouse_id' => null,
    ],
    'documents' => [
        'invoice_print_template' => null,
        'shipment_note_template' => null,
        'grn_note_template' => null,
    ],
    'general' => [
        'company_locale' => 'tr_TR',
        'timezone' => 'Europe/Istanbul',
        'decimal_precision' => 2,       // 2 veya 3
    ],
]
```

## Kullanım

```php
$service = app(\App\Modules\Settings\Domain\SettingsService::class);

$settings = $service->get($companyId);     // SettingsDTO
$defaults = $service->getDefaults($companyId); // ['payment_terms_days' => 30, ...]

$service->update($companyId, $settings, $userId); // versiyon +1, SettingsUpdated eventi tetikler
```

`SettingsReader` sözleşmesi, form varsayılanları gibi salt-okunur tüketiciler için `get` ve `getDefaults` metodlarını sunar. Güncellemeler `settings.manage` izni ile korunur, yazımda cache anahtarı `settings:{company_id}:v{version}` formatında yenilenir ve `SettingsAuditLogger` üzerinden basit audit kaydı oluşturulur.
