<?php

namespace App\Modules\Settings\Domain\Services;

use App\Modules\Settings\Domain\Events\SettingsChanged;
use Illuminate\Support\Facades\DB;

class SettingsVersionManager
{
    public function bumpVersion(int $companyId, string $area): void
    {
        DB::transaction(function () use ($companyId, $area): void {
            $record = DB::table('settings_companies')
                ->where('company_id', $companyId)
                ->lockForUpdate()
                ->first();

            if (! $record) {
                return;
            }

            $version = ((int) $record->version) + 1;

            DB::table('settings_companies')
                ->where('company_id', $companyId)
                ->update([
                    'version' => $version,
                    'updated_at' => now(),
                ]);

            SettingsChanged::dispatch($companyId, $area, $version);
        });
    }
}
