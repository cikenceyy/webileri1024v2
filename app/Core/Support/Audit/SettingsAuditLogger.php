<?php

namespace App\Core\Support\Audit;

use App\Modules\Settings\Domain\Events\SettingsUpdated;
use Illuminate\Support\Facades\Log;

class SettingsAuditLogger
{
    public function __invoke(SettingsUpdated $event): void
    {
        Log::info('settings.updated', [
            'company_id' => $event->companyId,
            'updated_by' => $event->updatedBy,
            'version' => $event->version,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
