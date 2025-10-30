<?php

namespace App\Core\Reports\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

/**
 * Üretilmiş cold rapor snapshot'larının metadata kayıtları.
 */
class ReportSnapshot extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'report_key',
        'params_hash',
        'status',
        'rows',
        'error',
        'generated_at',
        'valid_until',
        'storage_path',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'generated_at' => 'datetime',
        'valid_until' => 'datetime',
    ];
}
