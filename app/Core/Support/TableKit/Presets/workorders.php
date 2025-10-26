<?php

return [
    'columns' => [
        [
            'key' => 'doc_no',
            'label' => 'İş Emri',
            'type' => 'text',
            'sortable' => true,
            'filterable' => true,
        ],
        [
            'key' => 'product',
            'label' => 'Ürün',
            'type' => 'text',
            'filterable' => true,
        ],
        [
            'key' => 'status',
            'label' => 'Durum',
            'type' => 'badge',
            'filterable' => true,
            'enum' => [
                'planning' => 'Planlama',
                'released' => 'Serbest',
                'in_progress' => 'Üretimde',
                'completed' => 'Tamamlandı',
            ],
        ],
        [
            'key' => 'planned_start',
            'label' => 'Başlangıç',
            'type' => 'date',
            'sortable' => true,
            'filterable' => true,
            'options' => [
                'preformatted' => 'planned_start_human',
            ],
        ],
        [
            'key' => 'planned_end',
            'label' => 'Bitiş',
            'type' => 'date',
            'sortable' => true,
            'filterable' => true,
            'options' => [
                'preformatted' => 'planned_end_human',
            ],
        ],
    ],
    'options' => [
        'default_sort' => '-planned_start',
    ],
];
