<?php

namespace App\Core\Cache\Listeners;

use App\Core\Cache\InvalidationService;
use function currentCompanyId;

/**
 * Rol veya izin değişikliklerinde yetki önbelleğini temizler.
 */
class FlushPermissionsCache
{
    public function __construct(private readonly InvalidationService $cache)
    {
    }

    public function handle(object $event): void
    {
        $companyId = $this->extractCompanyId($event);

        $this->cache->flushTags(['permissions'], $companyId, [
            'reason' => 'permissions.updated',
            'event' => $event::class,
        ]);
    }

    private function extractCompanyId(object $event): ?int
    {
        foreach (['companyId', 'company_id', 'teamId', 'team_id', 'tenantId', 'tenant_id'] as $property) {
            if (isset($event->{$property}) && is_numeric($event->{$property})) {
                return (int) $event->{$property};
            }
        }

        if (method_exists($event, 'teamId')) {
            $teamId = $event->teamId();
            if (is_numeric($teamId)) {
                return (int) $teamId;
            }
        }

        $current = currentCompanyId();

        return $current ? (int) $current : null;
    }
}
