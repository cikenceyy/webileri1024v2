<?php

namespace App\Cms\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CmsContent extends Model
{
    protected $table = 'cms_contents';

    protected $fillable = [
        'company_id',
        'page',
        'locale',
        'data',
        'updated_by',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}
