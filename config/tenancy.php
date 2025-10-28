<?php

return [
    'strip_www' => env('TENANCY_STRIP_WWW', true),
    'cache_seconds' => env('TENANCY_DOMAIN_CACHE', 300),
    'local_fallback_company_id' => env('TENANT_FALLBACK_COMPANY_ID'),
    'cloud_enabled' => env('CLOUD_TEANANT', false),
];
