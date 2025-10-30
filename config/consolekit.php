<?php

/**
 * ConsoleKit ayarları: polling aralıkları, bulk işlem sınırları vb.
 */
return [
    'polling_interval_seconds' => env('CONSOLEKIT_POLLING', 15),
    'bulk' => [
        'max_concurrent' => env('CONSOLEKIT_BULK_MAX', 2),
    ],
];
