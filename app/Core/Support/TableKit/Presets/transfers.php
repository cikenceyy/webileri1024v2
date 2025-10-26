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
            'key' => 'from',
            'label' => 'Kaynak Depo',
            'type' => 'text',
            'filterable' => true,
        ],
        [
            'key' => 'to',
            'label' => 'Hedef Depo',
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
                'posted' => 'Tamamlandı',
                'in_transit' => 'Yolda',
            ],
        ],
        [
            'key' => 'created_at',
            'label' => 'Tarih',
            'type' => 'date',
            'sortable' => true,
            'filterable' => true,
            'options' => [
                'preformatted' => 'created_at_human',
            ],
        ],
        [
            'key' => 'actions',
            'label' => 'İşlemler',
            'type' => 'actions',
        ],
    ],
    'options' => [
        'default_sort' => '-created_at',
    ],
];
