<?php

namespace App\Modules\Settings\Domain\Models;

class DocumentTemplate extends SettingsModel
{
    protected $table = 'settings_document_templates';

    protected static string $settingsArea = 'documents';

    protected $fillable = [
        'company_id',
        'code',
        'print_header_html',
        'print_footer_html',
        'watermark_text',
        'created_by',
        'updated_by',
    ];
}
