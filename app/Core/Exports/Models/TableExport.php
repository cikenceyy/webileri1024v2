<?php

namespace App\Core\Exports\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TableKit tabanlı export isteklerinin durumunu saklayan model.
 * Amaç: Kuyruktaki işlemlerin ilerlemesini ve dosya yolunu izlemek.
 */
class TableExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'table_key',
        'format',
        'status',
        'params',
        'row_count',
        'progress',
        'file_path',
        'error',
        'retention_until',
    ];

    protected $casts = [
        'params' => 'array',
        'retention_until' => 'datetime',
        'row_count' => 'int',
        'progress' => 'int',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_DONE = 'done';
    public const STATUS_FAILED = 'failed';
}
