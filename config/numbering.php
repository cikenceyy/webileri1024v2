<?php

return [
    'defaults' => [
        'INV' => [
            'prefix' => 'INV',
            'padding' => 4,
            'reset_period' => 'yearly',
            'table' => 'invoices',
            'column' => 'invoice_no',
        ],
        'SO' => [
            'prefix' => 'SO',
            'padding' => 4,
            'reset_period' => 'yearly',
            'table' => 'orders',
            'column' => 'order_no',
        ],
        'PO' => [
            'prefix' => 'PO',
            'padding' => 4,
            'reset_period' => 'yearly',
            'table' => 'purchase_orders',
            'column' => 'po_number',
        ],
        'WO' => [
            'prefix' => 'WO',
            'padding' => 4,
            'reset_period' => 'monthly',
            'table' => 'work_orders',
            'column' => 'doc_no',
        ],
    ],
];
