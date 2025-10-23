<?php

namespace App\Modules\Settings\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Modules\Settings\Domain\Services\SettingsVersionManager;
use Illuminate\Database\Eloquent\Model;

abstract class SettingsModel extends Model
{
    use BelongsToCompany;

    /**
     * The area identifier used for SettingsChanged events.
     */
    protected static string $settingsArea = 'settings';

    protected static function booted(): void
    {
        static::saved(function (self $model): void {
            if (! $model->exists) {
                return;
            }

            app(SettingsVersionManager::class)->bumpVersion((int) $model->company_id, static::$settingsArea);
        });

        static::deleted(function (self $model): void {
            app(SettingsVersionManager::class)->bumpVersion((int) $model->company_id, static::$settingsArea);
        });
    }
}
