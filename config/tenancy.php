<?php

/**
 * Tenancy davranışını yöneten çekirdek ayarlar.
 *
 * cloud_enabled: Production/Cloud ortamında fallback kapatılır ve eşleşmeyen hostlar 404 döner.
 * domain_cache_ttl: Kiracı domain çözümlemelerinin saniye bazında önbellek süresi (0 = cache yok).
 * strip_www: Host çözümlemesinde "www" ön eki varyasyonlarının otomatik kontrol edilip edilmeyeceği.
 * local_fallback_company_id: Local ortamda eşleşme bulunamazsa düşülecek şirket kimliği (null = devre dışı).
 */
return [
    'cloud_enabled' => env('CLOUD_ENABLED', false),
    'domain_cache_ttl' => env('TENANCY_DOMAIN_CACHE', 300),
    'strip_www' => env('TENANCY_STRIP_WWW', true),
    'local_fallback_company_id' => env('TENANT_FALLBACK_COMPANY_ID'),
];
