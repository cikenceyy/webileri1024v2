<?php

return [
    'sequencer' => [
        'v2' => env('SEQUENCER_V2', true),
    ],
    'idempotency' => [
        'enforced' => env('POST_IDEMPOTENCY', true),
    ],
    'legacy_routing' => [
        'inventory_pricelists' => true,
        'inventory_bom' => true,
    ],
    'finance' => [
        'collections_console' => false,
        'reports_center' => false,
        'aging' => false,
        'ap_review' => false,
        'payment_suggestions' => false,
    ],
    'marketing' => [
        'returns' => true,
        'pricelists_bulk_update' => true,
    ],
    'logistics' => [
        'print' => true,
        'variance_reason_codes' => true,
        'quality_blocking' => false,
    ],
    'consoles' => [
        'o2c' => true,
        'p2p' => true,
        'mto' => true,
        'replenish' => true,
        'returns' => true,
        'quality' => true,
        'closeout' => true,
    ],
];
