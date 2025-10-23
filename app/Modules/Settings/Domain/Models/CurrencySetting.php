<?php

namespace App\Modules\Settings\Domain\Models;

class CurrencySetting extends SettingsModel
{
    protected $table = 'settings_currency';

    protected static string $settingsArea = 'currency';

    protected $fillable = [
        'company_id',
        'base_currency',
        'precision_price',
        'exchange_policy',
        'created_by',
        'updated_by',
    ];
}
