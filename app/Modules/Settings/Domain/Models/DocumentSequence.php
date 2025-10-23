<?php

namespace App\Modules\Settings\Domain\Models;

class DocumentSequence extends SettingsModel
{
    protected $table = 'settings_sequences';

    protected static string $settingsArea = 'sequences';

    protected $fillable = [
        'company_id',
        'doc_type',
        'prefix',
        'zero_pad',
        'next_no',
        'reset_rule',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'zero_pad' => 'int',
        'next_no' => 'int',
    ];
}
