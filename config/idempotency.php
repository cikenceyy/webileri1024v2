<?php

return [
    'enabled' => env('POST_IDEMPOTENCY', true),
    'header' => env('IDEMPOTENCY_HEADER', 'Idempotency-Key'),
    'cache_store' => env('IDEMPOTENCY_CACHE_STORE'),
    'ttl' => (int) env('IDEMPOTENCY_TTL', 600),
];
