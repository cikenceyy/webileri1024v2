<?php

namespace App\Core\Settings\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

/**
 * E-posta gönderimlerini şirket bazında raporlayan log modelidir.
 */
class CompanyEmailLog extends Model
{
    protected $table = 'company_email_logs';

    protected $fillable = [
        'company_id',
        'subject',
        'status',
        'recipients',
        'meta',
    ];

    protected $casts = [
        'recipients' => AsArrayObject::class,
        'meta' => AsArrayObject::class,
    ];
}
