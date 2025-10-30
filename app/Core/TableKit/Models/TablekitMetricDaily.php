<?php

namespace App\Core\TableKit\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TableKit günlük özet metriklerini temsil eder.
 * Amaç: Gün başına performans eğilimlerini raporlamak.
 */
class TablekitMetricDaily extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'table_key',
        'date',
        'request_count',
        'avg_total_time_ms',
        'p95_total_time_ms',
        'avg_query_count',
        'avg_db_time_ms',
        'cache_hit_ratio',
    ];

    protected $casts = [
        'date' => 'date',
        'cache_hit_ratio' => 'float',
    ];
}
