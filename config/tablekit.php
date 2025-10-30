<?php

/**
 * TableKit varsayılan ayarlarını ve export limitlerini merkezi konfigürasyonda tutar.
 */

return [
    /**
     * Modül bağımsız varsayılan tablo davranışları.
     */
    'defaults' => [
        'default_sort' => '-created_at',
        'page_size' => 25,
        'max_page_size' => 200,
        'row_density' => 'normal',
        'visible_columns' => [],
        'enable_heavy_filters' => true,
        'export_formats' => ['csv', 'xlsx'],
        'export_max_rows' => 100000,
    ],
];
