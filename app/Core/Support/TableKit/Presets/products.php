<?php

return [
    'columns' => [
        [
            'key' => 'sku',
            'label' => 'Stok Kodu',
            'type' => 'text',
            'sortable' => true,
            'filterable' => true,
        ],
        [
            'key' => 'name',
            'label' => 'Ürün',
            'type' => 'text',
            'filterable' => true,
        ],
        [
            'key' => 'category',
            'label' => 'Kategori',
            'type' => 'chip',
            'filterable' => true,
            'options' => [
                'preformatted' => 'category_chain',
            ],
        ],
        [
            'key' => 'status',
            'label' => 'Durum',
            'type' => 'badge',
            'filterable' => true,
        ],
        [
            'key' => 'stock_signal',
            'label' => 'Stok Sinyali',
            'type' => 'signal',
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
