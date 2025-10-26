<?php

return [
    'columns' => [
        [
            'key' => 'doc_no',
            'label' => 'GRN',
            'type' => 'text',
            'sortable' => true,
            'filterable' => true,
        ],
        [
            'key' => 'po',
            'label' => 'Satınalma Siparişi',
            'type' => 'text',
            'filterable' => true,
        ],
        [
            'key' => 'status',
            'label' => 'Durum',
            'type' => 'badge',
            'filterable' => true,
            'enum' => [
                'receiving' => 'Teslim alınıyor',
                'received' => 'Tamamlandı',
                'draft' => 'Taslak',
            ],
        ],
        [
            'key' => 'received_at',
            'label' => 'Teslim Tarihi',
            'type' => 'date',
            'sortable' => true,
            'filterable' => true,
            'options' => [
                'preformatted' => 'received_at_human',
            ],
        ],
        [
            'key' => 'actions',
            'label' => 'İşlemler',
            'type' => 'actions',
        ],
    ],
    'options' => [
        'default_sort' => '-received_at',
    ],
];
