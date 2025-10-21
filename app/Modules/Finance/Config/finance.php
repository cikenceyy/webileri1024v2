<?php

return [
    'default_currency' => env('FINANCE_DEFAULT_CURRENCY', 'TRY'),
    'supported_currencies' => ['TRY', 'USD', 'EUR'],
    'default_tax_rate' => env('FINANCE_DEFAULT_TAX', 20),
    'ap_price_tolerance_percent' => env('FINANCE_AP_TOLERANCE_PERCENT', 2),
    'ap_price_tolerance_absolute' => env('FINANCE_AP_TOLERANCE_ABSOLUTE', 0),
];
