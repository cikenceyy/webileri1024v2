<?php

namespace App\Core\Contracts;

use App\Modules\Settings\Domain\SettingsDTO;

interface SettingsReader
{
    public function get(int $companyId): SettingsDTO;

    /**
     * @return array<string, mixed>
     */
    public function getDefaults(int $companyId): array;
}
