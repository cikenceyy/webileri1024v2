<?php

namespace App\Core\Cache\Listeners;

use App\Core\Cache\InvalidationService;
use App\Modules\Settings\Domain\Events\SettingsUpdated;

/**
 * Ayarlar güncellendiğinde ilgili tenant önbelleğini temizler.
 */
class FlushSettingsCache
{
    public function __construct(private readonly InvalidationService $cache)
    {
    }

    public function handle(SettingsUpdated $event): void
    {
        $this->cache->flushTenant(
            $event->companyId,
            ['settings', 'menu'],
            [
                'reason' => 'settings.updated',
                'version' => $event->version,
                'updated_by' => $event->updatedBy,
            ]
        );
    }
}
