<?php

return [
    'presign_ttl_seconds' => env('DRIVE_PRESIGN_TTL', 300),
    'max_upload_bytes' => (int) env('DRIVE_MAX_UPLOAD_BYTES', 50 * 1024 * 1024),
    'disk' => env('DRIVE_DISK', config('filesystems.default', 'public')),
    'path_prefix' => env('DRIVE_PATH_PREFIX', 'companies/{company_id}/drive'),
    'default_storage_limit_bytes' => (int) env('DRIVE_DEFAULT_STORAGE_LIMIT_BYTES', 1_073_741_824),
    'categories' => [
        'documents' => [
            'mimes' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/csv',
                'text/plain',
                'application/zip',
            ],
            'ext' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt', 'zip'],
            'max' => 50 * 1024 * 1024,
        ],
        'media_products' => [
            'mimes' => ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'video/mp4', 'application/zip'],
            'ext' => ['jpg', 'jpeg', 'png', 'webp', 'svg', 'mp4', 'zip'],
            'max' => 25 * 1024 * 1024,
        ],
        'media_catalogs' => [
            'mimes' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/svg+xml',
                'video/mp4',
                'application/zip',
            ],
            'ext' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'webp', 'svg', 'mp4', 'zip'],
            'max' => 50 * 1024 * 1024,
        ],
        'pages' => [
            'mimes' => ['text/html', 'application/json', 'text/markdown', 'text/yaml', 'application/yaml', 'text/plain'],
            'ext' => ['html', 'htm', 'json', 'md', 'yaml', 'yml', 'txt'],
            'max' => 5 * 1024 * 1024,
        ],
    ],
];
