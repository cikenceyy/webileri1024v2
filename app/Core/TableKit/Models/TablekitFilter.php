<?php

namespace App\Core\TableKit\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TableKit kaydedilmiş filtre modelini temsil eder.
 * Amaç: Şirket/kullanıcı bazlı filtre ayarlarını JSON payload olarak saklamak.
 */
class TablekitFilter extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'table_key',
        'name',
        'is_default',
        'payload',
    ];

    protected $casts = [
        'is_default' => 'bool',
        'payload' => 'array',
    ];
}
