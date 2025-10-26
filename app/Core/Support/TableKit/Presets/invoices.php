<?php

return [
    'columns' => [
        [
            'key' => 'doc_no',
            'label' => 'Belge',
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
                'issued' => 'Kesildi',
                'paid' => 'Ödendi',
                'overdue' => 'Gecikmiş',
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
            'key' => 'due_at',
            'label' => 'Vade',
            'type' => 'date',
            'sortable' => true,
            'filterable' => true,
            'options' => [
                'preformatted' => 'due_at_human',
            ],
        ],
    ],
    'options' => [
        'default_sort' => '-due_at',
    ],
];
