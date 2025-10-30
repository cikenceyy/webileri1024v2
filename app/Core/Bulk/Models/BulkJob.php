<?php

namespace App\Core\Bulk\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

/**
 * Kuyrukta çalışan toplu işlemlerin durumlarını saklar.
 */
class BulkJob extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'user_id',
        'module',
        'action',
        'params',
        'status',
        'progress',
        'items_total',
        'items_done',
        'error',
        'started_at',
        'finished_at',
        'idempotency_key',
    ];

    protected $casts = [
        'params' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
