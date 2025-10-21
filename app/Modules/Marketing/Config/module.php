<?php

return [
    'default_tax_rate' => (float) env('MARKETING_DEFAULT_TAX_RATE', env('CRM_DEFAULT_TAX_RATE', 20)),
    'approvals' => [
        'min_margin_percent' => (float) env('MARKETING_MIN_MARGIN_PERCENT', env('CRM_MIN_MARGIN_PERCENT', 5.0)),
    ],
];
