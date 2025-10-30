<?php

namespace App\Core\Cache\Listeners;

use App\Core\Cache\InvalidationService;
use App\Core\Cache\TenantCacheManager;
use App\Core\Settings\Events\SettingsUpdated as CoreSettingsUpdated;
use App\Modules\Settings\Domain\Events\SettingsUpdated as LegacySettingsUpdated;

/**
 * Ayarlar güncellendiğinde ilgili tenant önbelleğini temizler.
 */
class FlushSettingsCache
{
    public function __construct(
        private readonly InvalidationService $cache,
        private readonly TenantCacheManager $tenantCache,
    ) {
    }

    public function handle(CoreSettingsUpdated|LegacySettingsUpdated $event): void
    {
        $this->cache->flushTenant(
            $event->companyId,
            ['settings', 'menu'],
            [
                'reason' => 'settings.updated',
                'keys' => property_exists($event, 'keys') ? $event->keys : null,
                'version' => property_exists($event, 'version') ? $event->version : null,
                'updated_by' => $event->updatedBy ?? null,
            ]
        );

        $this->tenantCache->warmTenant($event->companyId, ['menu', 'sidebar', 'dashboard']);
    }
}
