<?php

namespace App\Cms\Models;

use Illuminate\Database\Eloquent\Model;

class CmsAudit extends Model
{
    protected $table = 'cms_audits';

    protected $fillable = [
        'company_id',
        'user_id',
        'page',
        'locale',
        'field',
        'before',
        'after',
    ];
}
