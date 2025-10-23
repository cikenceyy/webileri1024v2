<?php

namespace App\Modules\Settings\Application\Services;

use App\Modules\Settings\Application\DTO\SettingsDTO;

interface SettingsServiceInterface
{
    public function companySettings(int $companyId): SettingsDTO;
}
