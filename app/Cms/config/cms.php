<?php

return [
    'pages' => [
        'home' => [
            'label' => 'Home',
            'description' => 'Homepage hero, USP grid, CTA band and automated product/catalog highlights.',
            'blocks' => [
                'hero' => [
                    'label' => 'Hero',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Alt Başlık', 'rows' => 3, 'max_length' => 360],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA Metni'],
                        'cta_link' => ['type' => 'link', 'label' => 'CTA Linki'],
                        'image' => ['type' => 'image', 'label' => 'Görsel', 'hint' => '16:9, <120KB', 'max' => 120, 'accept' => 'image/jpeg,image/png,image/webp'],
                    ],
                ],
                'usp_grid' => [
                    'label' => 'USP Grid',
                    'repeater' => true,
                    'fields' => [
                        'icon' => ['type' => 'image', 'label' => 'İkon', 'hint' => '64x64, <70KB', 'max' => 70, 'accept' => 'image/png,image/webp'],
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'description' => ['type' => 'textarea', 'label' => 'Açıklama', 'rows' => 3, 'max_length' => 320],
                    ],
                ],
                'mini_products' => [
                    'label' => 'Mini Products (auto)',
                    'fields' => [],
                ],
                'mini_catalogs' => [
                    'label' => 'Mini Catalogs (auto)',
                    'fields' => [],
                ],
                'cta_band' => [
                    'label' => 'CTA Band',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA Metni'],
                        'cta_link' => ['type' => 'link', 'label' => 'CTA Linki'],
                    ],
                ],
            ],
        ],
        'corporate' => [
            'label' => 'Corporate',
            'description' => 'Corporate story hero and media split layout.',
            'blocks' => [
                'hero' => [
                    'label' => 'Hero',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Alt Başlık', 'rows' => 3, 'max_length' => 360],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA Metni'],
                        'cta_link' => ['type' => 'link', 'label' => 'CTA Linki'],
                        'image' => ['type' => 'image', 'label' => 'Görsel', 'hint' => '16:9, <120KB', 'max' => 120, 'accept' => 'image/jpeg,image/png,image/webp'],
                    ],
                ],
                'intro' => [
                    'label' => 'Introduction',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'text' => ['type' => 'multiline', 'label' => 'Metin', 'rows' => 4, 'max_length' => 600],
                        'image' => ['type' => 'image', 'label' => 'Görsel', 'hint' => '3:2, <120KB', 'max' => 120, 'accept' => 'image/jpeg,image/png,image/webp'],
                    ],
                ],
            ],
        ],
        'contact' => [
            'label' => 'Contact',
            'description' => 'Contact hero and cards for offices, map and form copy.',
            'blocks' => [
                'hero' => [
                    'label' => 'Hero',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Alt Başlık', 'rows' => 3, 'max_length' => 360],
                    ],
                ],
                'coords' => [
                    'label' => 'Contact Coordinates',
                    'fields' => [
                        'address' => ['type' => 'textarea', 'label' => 'Adres', 'rows' => 4, 'max_length' => 360],
                        'phone' => ['type' => 'text', 'label' => 'Telefon'],
                        'email' => ['type' => 'text', 'label' => 'E-posta'],
                        'map_embed' => ['type' => 'textarea', 'label' => 'Harita Embed', 'rows' => 3, 'hint' => 'Google Maps iframe'],
                    ],
                ],
            ],
        ],
        'kvkk' => [
            'label' => 'KVKK',
            'description' => 'KVKK hero, body copy and optional PDF attachment.',
            'blocks' => [
                'hero' => [
                    'label' => 'Hero',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Alt Başlık', 'rows' => 3, 'max_length' => 360],
                    ],
                ],
                'body' => [
                    'label' => 'Body',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'text' => ['type' => 'multiline', 'label' => 'Metin', 'rows' => 8, 'max_length' => 2000],
                    ],
                ],
                'attachment' => [
                    'label' => 'Attachment',
                    'fields' => [
                        'file' => ['type' => 'file', 'label' => 'PDF', 'hint' => 'PDF, <10MB', 'max' => 10240],
                    ],
                ],
            ],
        ],
        'catalogs' => [
            'label' => 'Catalogs',
            'description' => 'Catalog hero and downloadable catalog list.',
            'blocks' => [
                'hero' => [
                    'label' => 'Hero',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Alt Başlık', 'rows' => 3, 'max_length' => 360],
                    ],
                ],
                'list' => [
                    'label' => 'Catalog List',
                    'repeater' => true,
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'cover' => ['type' => 'image', 'label' => 'Kapak', 'hint' => '4:3, <70KB', 'max' => 70, 'accept' => 'image/jpeg,image/png,image/webp'],
                        'file' => ['type' => 'file', 'label' => 'PDF', 'hint' => 'PDF, <10MB', 'max' => 10240],
                    ],
                ],
            ],
        ],
        'products' => [
            'label' => 'Products',
            'description' => 'Product listing hero copy (product data auto from inventory).',
            'blocks' => [
                'hero' => [
                    'label' => 'Hero',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Alt Başlık', 'rows' => 3, 'max_length' => 360],
                    ],
                ],
            ],
        ],
        'product_show' => [
            'label' => 'Product Show SEO',
            'blocks' => [],
        ],
    ],
    'seo_fields' => [
        'meta_title',
        'meta_description',
        'og_image',
    ],
    'emails' => [
        'info_email' => null,
        'notify_email' => null,
    ],
];
