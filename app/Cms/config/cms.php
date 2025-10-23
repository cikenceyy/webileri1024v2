<?php

return [
    'pages' => [
        'home' => [
            'label' => 'Home',
            'description' => 'Homepage hero, USP grid, CTA band and automated product/catalog highlights.',
            'blocks' => [
                'hero' => [
                    'label' => 'Hero',
                    'help' => '16:9 görsel 1600x900 çözünürlüğünde ve 120KB altında olmalıdır. CTA bağlantısı https:// veya site içi rota şeklinde girilebilir.',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Alt Başlık', 'rows' => 3, 'max_length' => 360],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA Metni', 'hint' => '12–32 karakter önerilir'],
                        'cta_link' => ['type' => 'link', 'label' => 'CTA Linki'],
                        'image' => ['type' => 'image', 'label' => 'Görsel', 'hint' => '16:9 • 1600x900 önerilir • <120KB (WebP)', 'max' => 120, 'accept' => 'image/jpeg,image/png,image/webp'],
                    ],
                ],
                'usp_grid' => [
                    'label' => 'USP Grid',
                    'repeater' => true,
                    'help' => 'İkonlar 64x64 piksel, başlık en fazla 3 kelime, açıklama 90–120 karakter aralığında olmalıdır.',
                    'fields' => [
                        'icon' => ['type' => 'image', 'label' => 'İkon', 'hint' => '64x64, <70KB (PNG/WebP)', 'max' => 70, 'accept' => 'image/png,image/webp'],
                        'title' => ['type' => 'text', 'label' => 'Başlık', 'hint' => 'En fazla 3 kelime'],
                        'description' => ['type' => 'textarea', 'label' => 'Açıklama', 'rows' => 3, 'max_length' => 320, 'hint' => '90–120 karakter önerilir'],
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
                    'help' => 'CTA metni 1-2 kelime, bağlantı ise iletişim veya özel rota olabilir.',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA Metni', 'hint' => '16–24 karakter önerilir'],
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
                        'image' => ['type' => 'image', 'label' => 'Görsel', 'hint' => '16:9 • 1600x900 önerilir • <120KB (WebP)', 'max' => 120, 'accept' => 'image/jpeg,image/png,image/webp'],
                    ],
                ],
                'intro' => [
                    'label' => 'Introduction',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'text' => ['type' => 'multiline', 'label' => 'Metin', 'rows' => 4, 'max_length' => 600, 'hint' => '300–600 karakter arası'],
                        'image' => ['type' => 'image', 'label' => 'Görsel', 'hint' => '3:2 • 1200x800 önerilir • <120KB (WebP)', 'max' => 120, 'accept' => 'image/jpeg,image/png,image/webp'],
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
                        'address' => ['type' => 'textarea', 'label' => 'Adres', 'rows' => 4, 'max_length' => 360, 'hint' => 'Satır başına 60 karakteri aşmayın'],
                        'phone' => ['type' => 'text', 'label' => 'Telefon', 'hint' => '+90 formatında girin'],
                        'email' => ['type' => 'text', 'label' => 'E-posta'],
                        'map_embed' => ['type' => 'textarea', 'label' => 'Harita Embed', 'rows' => 3, 'hint' => 'Google Maps iframe kodunu yapıştırın'],
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
                        'text' => ['type' => 'multiline', 'label' => 'Metin', 'rows' => 8, 'max_length' => 2000, 'hint' => '2000 karaktere kadar özet metin'],
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
                    'help' => 'Yeni katalog kartları ekleyebilir, sıralamayı düzenleyebilir ve PDF bağlantılarını güncelleyebilirsiniz.',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık', 'hint' => 'En fazla 40 karakter'],
                        'cover' => ['type' => 'image', 'label' => 'Kapak', 'hint' => '4:3 • 1200x900 önerilir • <70KB (WebP)', 'max' => 70, 'accept' => 'image/jpeg,image/png,image/webp'],
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
                        'subtitle' => ['type' => 'textarea', 'label' => 'Alt Başlık', 'rows' => 3, 'max_length' => 360, 'hint' => '160–220 karakter önerilir'],
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
    'analytics' => [
        'endpoint' => null,
    ],
];
