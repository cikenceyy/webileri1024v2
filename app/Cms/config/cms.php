<?php

return [
    'pages' => [
        'home' => [
            'label' => 'Home',
            'blocks' => [
                'hero' => [
                    'label' => 'Hero',
                    'fields' => [
                        'title' => ['type' => 'text'],
                        'subtitle' => ['type' => 'text'],
                        'cta_text' => ['type' => 'text'],
                        'cta_link' => ['type' => 'link'],
                        'image' => ['type' => 'image'],
                    ],
                ],
                'usp_grid' => [
                    'label' => 'USP Grid',
                    'repeater' => true,
                    'fields' => [
                        'icon' => ['type' => 'image'],
                        'title' => ['type' => 'text'],
                        'description' => ['type' => 'text'],
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
                        'title' => ['type' => 'text'],
                        'cta_text' => ['type' => 'text'],
                        'cta_link' => ['type' => 'link'],
                    ],
                ],
            ],
        ],
        'corporate' => [
            'label' => 'Corporate',
            'blocks' => [
                'intro' => [
                    'label' => 'Introduction',
                    'fields' => [
                        'title' => ['type' => 'text'],
                        'text' => ['type' => 'text'],
                        'image' => ['type' => 'image'],
                    ],
                ],
            ],
        ],
        'contact' => [
            'label' => 'Contact',
            'blocks' => [
                'coords' => [
                    'label' => 'Contact Coordinates',
                    'fields' => [
                        'address' => ['type' => 'textarea'],
                        'phone' => ['type' => 'text'],
                        'email' => ['type' => 'text'],
                        'map_embed' => ['type' => 'text'],
                    ],
                ],
            ],
        ],
        'kvkk' => [
            'label' => 'KVKK',
            'blocks' => [
                'body' => [
                    'label' => 'Body',
                    'fields' => [
                        'title' => ['type' => 'text'],
                        'text' => ['type' => 'text'],
                    ],
                ],
                'attachment' => [
                    'label' => 'Attachment',
                    'fields' => [
                        'file' => ['type' => 'file'],
                    ],
                ],
            ],
        ],
        'catalogs' => [
            'label' => 'Catalogs',
            'blocks' => [
                'list' => [
                    'label' => 'Catalog List',
                    'repeater' => true,
                    'fields' => [
                        'title' => ['type' => 'text'],
                        'cover' => ['type' => 'image'],
                        'file' => ['type' => 'file'],
                    ],
                ],
            ],
        ],
        'products' => [
            'label' => 'Products',
            'blocks' => [],
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
