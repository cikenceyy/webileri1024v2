<?php

namespace App\Core\Settings\Events;

/**
 * Ayar değişikliklerini dinleyicilere bildiren basit domain olayı.
 */
class SettingsUpdated
{
    /**
     * @param  array<int, string>  $keys
     */
    public function __construct(
        public int $companyId,
        public array $keys,
        public ?int $updatedBy = null,
    ) {
    }
}
