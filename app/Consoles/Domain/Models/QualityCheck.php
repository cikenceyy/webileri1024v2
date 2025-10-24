<?php

namespace App\Consoles\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class QualityCheck extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'subject_type',
        'subject_id',
        'direction',
        'result',
        'notes',
        'checked_at',
        'checked_by',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];
}
