<?php

namespace App\Modules\Settings\Domain\Events;

use App\Modules\Settings\Domain\SettingsDTO;

class SettingsUpdated
{
    public function __construct(
        public int $companyId,
        public int $updatedBy,
        public int $version,
        public SettingsDTO $settings,
    ) {
    }
}
