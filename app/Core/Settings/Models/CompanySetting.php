<?php

namespace App\Core\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * company_settings tablosundaki tekil kayıtları temsil eder.
 * Anahtar bazlı ayarlar company_id + key çiftine göre saklanır.
 */
class CompanySetting extends Model
{
    protected $table = 'company_settings';

    protected $fillable = [
        'company_id',
        'key',
        'type',
        'value',
        'updated_by',
    ];
}
