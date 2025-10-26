<?php

return [
    'columns' => [
        [
            'key' => 'doc_no',
            'label' => 'Sevkiyat',
            'type' => 'text',
            'sortable' => true,
            'filterable' => true,
        ],
        [
            'key' => 'step',
            'label' => 'Adım',
            'type' => 'badge',
            'filterable' => true,
            'enum' => [
                'picking' => 'Toplama',
                'packed' => 'Paketlendi',
                'shipped' => 'Yolda',
                'delivered' => 'Teslim edildi',
            ],
        ],
        [
            'key' => 'progress',
            'label' => 'İlerleme',
            'type' => 'number',
            'sortable' => true,
            'options' => [
                'preformatted' => 'progress_percent',
            ],
        ],
        [
            'key' => 'status',
            'label' => 'Durum',
            'type' => 'badge',
            'filterable' => true,
        ],
        [
            'key' => 'updated_at',
            'label' => 'Güncelleme',
            'type' => 'date',
            'sortable' => true,
            'filterable' => true,
            'options' => [
                'preformatted' => 'updated_at_human',
            ],
        ],
    ],
    'options' => [
        'default_sort' => '-updated_at',
    ],
];
