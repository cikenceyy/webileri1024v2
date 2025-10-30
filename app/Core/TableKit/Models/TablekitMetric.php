<?php

namespace App\Core\TableKit\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TableKit anlık metrik kaydı modelidir.
 * Amaç: Her liste isteği sonrası süre ve cache başarısını saklamak.
 */
class TablekitMetric extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'table_key',
        'ts',
        'rows',
        'query_count',
        'db_time_ms',
        'total_time_ms',
        'cache_hit',
        'filters_hash',
    ];

    protected $casts = [
        'ts' => 'datetime',
        'cache_hit' => 'bool',
    ];
}
