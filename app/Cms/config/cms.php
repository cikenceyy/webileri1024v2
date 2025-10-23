<?php

return [
    'pages' => [
        'home' => [
            'label' => 'Home',
            'description' => 'Homepage hero, USP grid, industries, process, stats, partners and automated highlights.',
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
                'industries' => [
                    'label' => 'Industries',
                    'repeater' => true,
                    'help' => '4–8 sektör kartı. İkonlar 64x64, açıklamalar 90–140 karakter olmalı.',
                    'fields' => [
                        'icon' => ['type' => 'image', 'label' => 'İkon', 'hint' => '64x64, gri ton • <60KB (PNG/WebP)', 'max' => 60, 'accept' => 'image/png,image/webp'],
                        'title' => ['type' => 'text', 'label' => 'Başlık', 'hint' => '3 kelimeyi aşmayın'],
                        'description' => ['type' => 'textarea', 'label' => 'Açıklama', 'rows' => 3, 'max_length' => 360],
                    ],
                ],
                'process_steps' => [
                    'label' => 'Process Steps',
                    'repeater' => true,
                    'help' => '3–5 adım. Opsiyonel görsel 4:3 oranında ve 90KB altında olmalı.',
                    'fields' => [
                        'step_no' => ['type' => 'text', 'label' => 'Adım Numarası', 'hint' => '1, 2, 3... şeklinde'],
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'description' => ['type' => 'textarea', 'label' => 'Açıklama', 'rows' => 3, 'max_length' => 360],
                        'image' => ['type' => 'image', 'label' => 'Görsel', 'hint' => '4:3 • 960x720 önerilir • <90KB (WebP)', 'max' => 90, 'accept' => 'image/jpeg,image/png,image/webp'],
                    ],
                ],
                'stats_band' => [
                    'label' => 'Stats Band',
                    'repeater' => true,
                    'help' => 'Örn. +25 / yıllık proje. 3–6 metrik ekleyin.',
                    'fields' => [
                        'value' => ['type' => 'text', 'label' => 'Değer', 'hint' => 'Kısa, çarpıcı sayı ya da %'],
                        'label' => ['type' => 'text', 'label' => 'Etiket', 'hint' => 'En fazla 40 karakter'],
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
                'partners' => [
                    'label' => 'Partners',
                    'repeater' => true,
                    'help' => 'Logo 160x80 gri ton, <40KB. Opsiyonel link ekleyebilirsiniz.',
                    'fields' => [
                        'name' => ['type' => 'text', 'label' => 'İş Ortağı Adı', 'hint' => 'Erişilebilirlik için gereklidir'],
                        'logo' => ['type' => 'image', 'label' => 'Logo', 'hint' => '160x80 • gri ton • <40KB (PNG/WebP)', 'max' => 40, 'accept' => 'image/png,image/webp'],
                        'link' => ['type' => 'link', 'label' => 'Link', 'hint' => 'Opsiyonel kurum adresi'],
                    ],
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
            'description' => 'Brand story, mission, capabilities, standards and timeline.',
            'blocks' => [
                'page_hero' => [
                    'label' => 'Page Hero',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'intro' => ['type' => 'multiline', 'label' => 'Giriş', 'rows' => 4, 'max_length' => 420, 'hint' => '2 paragraf önerilir'],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA Metni', 'hint' => 'Opsiyonel'],
                        'cta_link' => ['type' => 'link', 'label' => 'CTA Linki'],
                        'image' => ['type' => 'image', 'label' => 'Görsel', 'hint' => '16:9 • 1600x900 önerilir • <120KB (WebP)', 'max' => 120, 'accept' => 'image/jpeg,image/png,image/webp'],
                    ],
                ],
                'mission_vision' => [
                    'label' => 'Mission & Vision',
                    'fields' => [
                        'mission' => ['type' => 'multiline', 'label' => 'Misyon', 'rows' => 4, 'max_length' => 420],
                        'vision' => ['type' => 'multiline', 'label' => 'Vizyon', 'rows' => 4, 'max_length' => 420],
                    ],
                ],
                'mission_values' => [
                    'label' => 'Core Values',
                    'repeater' => true,
                    'help' => '3–6 değer girin. Başlık 2 kelimeyi aşmasın.',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Değer Başlığı'],
                        'description' => ['type' => 'textarea', 'label' => 'Değer Açıklaması', 'rows' => 3, 'max_length' => 320],
                    ],
                ],
                'media_left' => [
                    'label' => 'Media Left Split',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'text' => ['type' => 'multiline', 'label' => 'Metin', 'rows' => 6, 'max_length' => 600, 'hint' => 'Paragrafları 70–80 karakter satır uzunluğu hedefiyle yazın'],
                        'image' => ['type' => 'image', 'label' => 'Görsel', 'hint' => '4:3 • 1200x900 önerilir • <100KB (WebP)', 'max' => 100, 'accept' => 'image/jpeg,image/png,image/webp'],
                    ],
                ],
                'capabilities' => [
                    'label' => 'Capabilities',
                    'repeater' => true,
                    'help' => 'Makine parkı ve uzmanlıklar. 4–8 kart.',
                    'fields' => [
                        'icon' => ['type' => 'image', 'label' => 'İkon', 'hint' => '64x64, <60KB (PNG/WebP)', 'max' => 60, 'accept' => 'image/png,image/webp'],
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'description' => ['type' => 'textarea', 'label' => 'Açıklama', 'rows' => 3, 'max_length' => 320],
                    ],
                ],
                'quality_standards' => [
                    'label' => 'Quality Standards',
                    'repeater' => true,
                    'help' => 'Sertifika adını, kodunu ve opsiyonel görselini ekleyin.',
                    'fields' => [
                        'name' => ['type' => 'text', 'label' => 'Sertifika Adı'],
                        'code' => ['type' => 'text', 'label' => 'Kod/Numara', 'hint' => 'Örn. ISO 9001:2015'],
                        'text' => ['type' => 'textarea', 'label' => 'Açıklama', 'rows' => 3, 'max_length' => 280],
                        'image' => ['type' => 'image', 'label' => 'Belge Görseli', 'hint' => '1:1 veya 4:5 • <90KB (WebP)', 'max' => 90, 'accept' => 'image/jpeg,image/png,image/webp'],
                    ],
                ],
                'milestones_timeline' => [
                    'label' => 'Milestones Timeline',
                    'repeater' => true,
                    'help' => 'Zaman çizelgesi için yıl ve kısa açıklama ekleyin.',
                    'fields' => [
                        'year' => ['type' => 'text', 'label' => 'Yıl'],
                        'label' => ['type' => 'text', 'label' => 'Başlık'],
                        'description' => ['type' => 'textarea', 'label' => 'Açıklama', 'rows' => 3, 'max_length' => 320],
                    ],
                ],
                'partners_band' => [
                    'label' => 'Partners Band',
                    'repeater' => true,
                    'help' => 'Logo 160x80 gri ton, <40KB. Ad alanı erişilebilirlik için zorunludur.',
                    'fields' => [
                        'name' => ['type' => 'text', 'label' => 'İş Ortağı Adı'],
                        'logo' => ['type' => 'image', 'label' => 'Logo', 'hint' => '160x80 • gri ton • <40KB (PNG/WebP)', 'max' => 40, 'accept' => 'image/png,image/webp'],
                        'link' => ['type' => 'link', 'label' => 'Link'],
                    ],
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
        'contact' => [
            'label' => 'Contact',
            'description' => 'Contact hero, cards, map trigger and form guidance.',
            'blocks' => [
                'page_hero' => [
                    'label' => 'Page Hero',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Alt Başlık', 'rows' => 3, 'max_length' => 360],
                    ],
                ],
                'contact_cards' => [
                    'label' => 'Contact Card',
                    'fields' => [
                        'address' => ['type' => 'textarea', 'label' => 'Adres', 'rows' => 4, 'max_length' => 360, 'hint' => 'Satır başına 60 karakteri aşmayın'],
                        'phone' => ['type' => 'text', 'label' => 'Telefon', 'hint' => '+90 formatında girin'],
                        'email' => ['type' => 'text', 'label' => 'E-posta'],
                        'hours' => ['type' => 'textarea', 'label' => 'Çalışma Saatleri', 'rows' => 2, 'max_length' => 160],
                    ],
                ],
                'social_links' => [
                    'label' => 'Sosyal Ağlar',
                    'repeater' => true,
                    'help' => 'Platform adı ve https:// bağlantısı ekleyin.',
                    'fields' => [
                        'name' => ['type' => 'text', 'label' => 'Platform Adı'],
                        'url' => ['type' => 'link', 'label' => 'URL'],
                    ],
                ],
                'map' => [
                    'label' => 'Map Embed',
                    'fields' => [
                        'map_embed' => ['type' => 'textarea', 'label' => 'Harita Embed', 'rows' => 3, 'hint' => 'Google Maps iframe kodunu yapıştırın'],
                    ],
                ],
                'form_copy' => [
                    'label' => 'Form Copy',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Form Başlığı', 'hint' => 'Opsiyonel'],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Form Alt Metni', 'rows' => 3, 'max_length' => 320],
                    ],
                ],
            ],
        ],
        'kvkk' => [
            'label' => 'KVKK',
            'description' => 'Structured KVKK summary, purposes, retention, rights and PDF.',
            'blocks' => [
                'page_hero' => [
                    'label' => 'Page Hero',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Alt Başlık', 'rows' => 3, 'max_length' => 360],
                    ],
                ],
                'summary' => [
                    'label' => 'Summary',
                    'fields' => [
                        'intro' => ['type' => 'multiline', 'label' => 'Özet', 'rows' => 6, 'max_length' => 1200],
                    ],
                ],
                'purposes' => [
                    'label' => 'Processing Purposes',
                    'repeater' => true,
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Amaç Başlığı'],
                        'description' => ['type' => 'textarea', 'label' => 'Açıklama', 'rows' => 4, 'max_length' => 480],
                    ],
                ],
                'retention' => [
                    'label' => 'Retention Periods',
                    'repeater' => true,
                    'fields' => [
                        'data_type' => ['type' => 'text', 'label' => 'Veri Türü'],
                        'period' => ['type' => 'text', 'label' => 'Saklama Süresi'],
                        'description' => ['type' => 'textarea', 'label' => 'Detay', 'rows' => 3, 'max_length' => 320],
                    ],
                ],
                'rights' => [
                    'label' => 'Data Subject Rights',
                    'repeater' => true,
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Hak Başlığı'],
                        'description' => ['type' => 'textarea', 'label' => 'Açıklama', 'rows' => 4, 'max_length' => 420],
                    ],
                ],
                'contact_privacy' => [
                    'label' => 'Privacy Contact',
                    'fields' => [
                        'email' => ['type' => 'text', 'label' => 'E-posta'],
                        'address' => ['type' => 'textarea', 'label' => 'Adres', 'rows' => 4, 'max_length' => 360],
                        'officer' => ['type' => 'text', 'label' => 'Sorumlu Kişi'],
                    ],
                ],
                'pdf' => [
                    'label' => 'PDF Attachment',
                    'fields' => [
                        'file' => ['type' => 'file', 'label' => 'PDF', 'hint' => 'PDF, <10MB', 'max' => 10240],
                    ],
                ],
            ],
        ],
        'catalogs' => [
            'label' => 'Catalogs',
            'description' => 'Catalog hero, filters and downloadable catalog list.',
            'blocks' => [
                'page_hero' => [
                    'label' => 'Page Hero',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Alt Başlık', 'rows' => 3, 'max_length' => 360],
                    ],
                ],
                'filters_year' => [
                    'label' => 'Year Filters',
                    'repeater' => true,
                    'help' => 'Yıl ya da kategori etiketleri. Filtre slug alanı URL dostu olmalı.',
                    'fields' => [
                        'label' => ['type' => 'text', 'label' => 'Etiket'],
                        'slug' => ['type' => 'text', 'label' => 'Slug', 'hint' => 'Küçük harf, tire ile'],
                    ],
                ],
                'list' => [
                    'label' => 'Catalog List',
                    'repeater' => true,
                    'help' => 'Yeni katalog kartları ekleyebilir, sıralamayı düzenleyebilir ve PDF bağlantılarını güncelleyebilirsiniz.',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık', 'hint' => 'En fazla 40 karakter'],
                        'year' => ['type' => 'text', 'label' => 'Yıl', 'hint' => 'Opsiyonel filtre eşleşmesi için'],
                        'cover' => ['type' => 'image', 'label' => 'Kapak', 'hint' => '4:3 • 1200x900 önerilir • <70KB (WebP)', 'max' => 70, 'accept' => 'image/jpeg,image/png,image/webp'],
                        'file' => ['type' => 'file', 'label' => 'PDF', 'hint' => 'PDF, <10MB', 'max' => 10240],
                    ],
                ],
            ],
        ],
        'products' => [
            'label' => 'Products',
            'description' => 'Product listing hero copy with optional discovery filters.',
            'blocks' => [
                'page_hero' => [
                    'label' => 'Page Hero',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Alt Başlık', 'rows' => 3, 'max_length' => 360, 'hint' => '160–220 karakter önerilir'],
                    ],
                ],
                'filters' => [
                    'label' => 'Discovery Filters',
                    'repeater' => true,
                    'help' => 'Filtre etiketleri ve slug değerleri ürün kategorileriyle eşleşmelidir.',
                    'fields' => [
                        'label' => ['type' => 'text', 'label' => 'Etiket'],
                        'slug' => ['type' => 'text', 'label' => 'Slug', 'hint' => 'Küçük harf, tire ile'],
                    ],
                ],
            ],
        ],
        'product_show' => [
            'label' => 'Product Show Enhancements',
            'blocks' => [
                'marketing_bullets' => [
                    'label' => 'Marketing Bullets',
                    'repeater' => true,
                    'help' => 'Ürünün faydalarını vurgulayan 4–8 maddelik liste.',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Başlık'],
                        'description' => ['type' => 'textarea', 'label' => 'Açıklama', 'rows' => 3, 'max_length' => 260],
                    ],
                ],
                'downloads' => [
                    'label' => 'Downloads',
                    'repeater' => true,
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Belge Başlığı'],
                        'file' => ['type' => 'file', 'label' => 'PDF', 'hint' => 'PDF, <10MB', 'max' => 10240],
                    ],
                ],
            ],
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
    'cache' => [
        'warm_pages' => true,
    ],
    'robots' => [
        'index_environments' => ['production'],
        'override' => env('CMS_ROBOTS_OVERRIDE'),
        'sitemap' => null,
    ],
];
