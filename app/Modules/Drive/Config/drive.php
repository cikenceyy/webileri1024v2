<?php

return [
    'presign_ttl_seconds' => (int) config('files.temporary_url_seconds', env('DRIVE_PRESIGN_TTL', 300)),
    'max_upload_bytes' => (int) (config('files.max_upload_megabytes', 50) * 1024 * 1024),
    'disk' => env('DRIVE_DISK', config('filesystems.default', 'local')),
    'path_prefix' => env('DRIVE_PATH_PREFIX', 'companies/{company_id}/drive'),
    'default_storage_limit_bytes' => (int) env('DRIVE_DEFAULT_STORAGE_LIMIT_BYTES', 1_073_741_824),
    'defaults' => [
        'module' => 'cms',
        'folder' => 'documents',
    ],
    'folders' => [
        'documents' => [
            'label' => 'Belgeler',
            'type' => 'document',
            'mimes' => collect(config('files.document_extensions', []))
                ->map(fn ($ext) => config('files.allowed_extensions.' . $ext, []))
                ->flatten()
                ->unique()
                ->values()
                ->all(),
            'ext' => config('files.document_extensions', []),
            'max' => (int) (config('files.max_upload_megabytes', 50) * 1024 * 1024),
        ],
        'media' => [
            'label' => 'Medya',
            'type' => 'media',
            'mimes' => collect(config('files.media_extensions', []))
                ->map(fn ($ext) => config('files.allowed_extensions.' . $ext, []))
                ->flatten()
                ->unique()
                ->values()
                ->all(),
            'ext' => config('files.media_extensions', []),
            'max' => (int) (config('files.max_upload_megabytes', 50) * 1024 * 1024),
        ],
        'products' => [
            'label' => 'Ürün Dosyaları',
            'type' => 'media',
            'mimes' => collect(['jpg', 'jpeg', 'png', 'webp', 'svg'])
                ->map(fn ($ext) => config('files.allowed_extensions.' . $ext, []))
                ->flatten()
                ->unique()
                ->values()
                ->all(),
            'ext' => ['jpg', 'jpeg', 'png', 'webp', 'svg'],
            'max' => 25 * 1024 * 1024,
        ],
    ],
    'modules' => [
        'cms' => [
            'label' => 'CMS',
            'folders' => ['documents', 'media'],
        ],
        'marketing' => [
            'label' => 'Marketing',
            'folders' => ['documents', 'media'],
        ],
        'finance' => [
            'label' => 'Finance',
            'folders' => ['documents', 'media'],
        ],
        'logistics' => [
            'label' => 'Logistics',
            'folders' => ['documents', 'media'],
        ],
        'inventory' => [
            'label' => 'Inventory',
            'folders' => ['documents', 'media', 'products'],
        ],
        'production' => [
            'label' => 'Production',
            'folders' => ['documents', 'media'],
        ],
        'hr' => [
            'label' => 'HR',
            'folders' => ['documents', 'media'],
        ],
    ],
];
