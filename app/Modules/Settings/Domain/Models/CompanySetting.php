<?php

namespace App\Modules\Settings\Domain\Models;

use App\Modules\Drive\Domain\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CompanySetting extends SettingsModel
{
    protected $table = 'settings_companies';

    protected static string $settingsArea = 'company';

    protected $fillable = [
        'company_id',
        'name',
        'legal_title',
        'tax_office',
        'tax_number',
        'website',
        'email',
        'phone',
        'logo_media_id',
        'version',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'version' => 'int',
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'company_id', 'company_id');
    }

    public function tax(): HasOne
    {
        return $this->hasOne(TaxProfile::class, 'company_id', 'company_id');
    }

    public function currency(): HasOne
    {
        return $this->hasOne(CurrencySetting::class, 'company_id', 'company_id');
    }

    public function defaults(): HasOne
    {
        return $this->hasOne(DefaultSetting::class, 'company_id', 'company_id');
    }

    public function sequences(): HasMany
    {
        return $this->hasMany(DocumentSequence::class, 'company_id', 'company_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DocumentTemplate::class, 'company_id', 'company_id');
    }

    public function logoMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_media_id');
    }
}
