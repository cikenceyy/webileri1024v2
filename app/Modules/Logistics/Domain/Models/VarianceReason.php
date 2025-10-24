<?php

namespace App\Modules\Logistics\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class VarianceReason extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];
}
