<?php

return [
    'columns' => [
        [
            'key' => 'doc_no',
            'label' => 'Sipariş',
            'type' => 'text',
            'sortable' => true,
            'filterable' => true,
        ],
        [
            'key' => 'customer',
            'label' => 'Müşteri',
            'type' => 'text',
            'filterable' => true,
        ],
        [
            'key' => 'status',
            'label' => 'Durum',
            'type' => 'badge',
            'filterable' => true,
            'enum' => [
                'draft' => 'Taslak',
                'approved' => 'Onaylandı',
                'fulfilled' => 'Tamamlandı',
                'cancelled' => 'İptal',
            ],
        ],
        [
            'key' => 'grand_total',
            'label' => 'Tutar',
            'type' => 'money',
            'sortable' => true,
            'options' => [
                'preformatted' => 'grand_total_formatted',
            ],
        ],
        [
            'key' => 'due_date',
            'label' => 'Termin',
            'type' => 'date',
            'sortable' => true,
            'filterable' => true,
            'options' => [
                'preformatted' => 'due_date_human',
            ],
        ],
    ],
    'options' => [
        'default_sort' => '-due_date',
    ],
];
