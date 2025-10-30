<?php

namespace App\Core\Cache\Warmers;

use App\Core\Cache\InvalidationService;
use App\Core\Cache\Keys;
use App\Core\Views\AdminSidebar;

/**
 * Admin navigasyon menüsünü tenant bazlı olarak ısıtır.
 * Beklenen veri: AdminSidebar::navigation() çıktısı (başlıklar, çocuk menüler).
 */
class MenuWarmer
{
    public function __construct(private readonly InvalidationService $cache)
    {
    }

    public function warm(int $companyId): void
    {
        $key = Keys::forTenant($companyId, ['menu', 'main'], 'v1');
        $ttl = (int) config('cache.ttl_profiles.warm', 900);

        $this->cache->rememberWithTags(
            $key,
            [sprintf('tenant:%d', $companyId), 'menu'],
            $ttl,
            static fn () => AdminSidebar::navigation(),
        );
    }
}
