<?php

namespace App\Core\Support\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyDomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'domain',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'bool',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
