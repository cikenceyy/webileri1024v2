<?php

namespace App\Modules\Settings\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use BelongsToCompany;

    protected $table = 'settings';

    protected $fillable = [
        'company_id',
        'data',
        'version',
        'updated_by',
    ];

    protected $casts = [
        'data' => 'array',
        'version' => 'integer',
    ];

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
