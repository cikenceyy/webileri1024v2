<?php

namespace App\Modules\Settings\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRate extends SettingsModel
{
    protected $table = 'settings_tax_rates';

    protected static string $settingsArea = 'tax';

    protected $fillable = [
        'company_id',
        'tax_id',
        'name',
        'rate',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'rate' => 'float',
        'is_active' => 'bool',
    ];

    public function tax(): BelongsTo
    {
        return $this->belongsTo(TaxProfile::class, 'tax_id');
    }
}
