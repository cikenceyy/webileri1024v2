<?php

namespace App\Modules\Settings\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxProfile extends SettingsModel
{
    protected $table = 'settings_tax';

    protected static string $settingsArea = 'tax';

    protected $fillable = [
        'company_id',
        'default_vat_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'default_vat_id' => 'int',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(TaxRate::class, 'tax_id');
    }

    public function defaultRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'default_vat_id');
    }
}
