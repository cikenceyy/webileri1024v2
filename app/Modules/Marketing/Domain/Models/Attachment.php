<?php

namespace App\Modules\Marketing\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'related_type',
        'related_id',
        'media_id',
        'uploaded_by',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Drive\Domain\Models\Media::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }
}
