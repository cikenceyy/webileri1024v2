<?php

namespace App\Core\Auth\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Yetki denemelerinin sonucunu saklayan basit model.
 */
class AuthAudit extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'action',
        'resource',
        'result',
        'ip_address',
        'user_agent',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
