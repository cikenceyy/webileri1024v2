<?php

namespace App\Modules\Drive\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modül kayıtlarına bağlanan Drive dosyalarını temsil eden pivot model.
 * Meta alanı JSON tutulur ve company_id ile kapsamlanır.
 */
class DriveAttachment extends Model
{
    protected $table = 'drive_attachments';

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}
