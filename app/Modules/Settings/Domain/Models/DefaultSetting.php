<?php

namespace App\Modules\Settings\Domain\Models;

class DefaultSetting extends SettingsModel
{
    protected $table = 'settings_defaults';

    protected static string $settingsArea = 'defaults';

    protected $fillable = [
        'company_id',
        'default_warehouse_id',
        'default_tax_id',
        'default_payment_terms',
        'default_print_template',
        'default_country',
        'default_city',
        'logistics_defaults',
        'finance_defaults',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'logistics_defaults' => 'array',
        'finance_defaults' => 'array',
    ];
}
