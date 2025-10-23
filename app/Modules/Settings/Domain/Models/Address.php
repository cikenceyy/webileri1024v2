<?php

namespace App\Modules\Settings\Domain\Models;

class Address extends SettingsModel
{
    protected $table = 'settings_addresses';

    protected static string $settingsArea = 'addresses';

    protected $fillable = [
        'company_id',
        'type',
        'country',
        'city',
        'district',
        'address_line',
        'postal_code',
        'is_default',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_default' => 'bool',
    ];
}
