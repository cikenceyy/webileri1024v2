<?php

namespace App\Core\Cache\Warmers;

use App\Core\Cache\InvalidationService;
use App\Core\Cache\Keys;
use App\Core\Views\AdminSidebar;

/**
 * Yan menüde kullanılan navigasyon ağacını ısıtır; erişim kontrolleri sonucun
 * içinde saklandığı için render sırasında yeniden hesaplanmaz.
 */
class SidebarWarmer
{
    public function __construct(private readonly InvalidationService $cache)
    {
    }

    public function warm(int $companyId): void
    {
        $key = Keys::forTenant($companyId, ['sidebar', 'navigation'], 'v1');
        $ttl = (int) config('cache.ttl_profiles.warm', 900);

        $this->cache->rememberWithTags(
            $key,
            [sprintf('tenant:%d', $companyId), 'sidebar', 'menu'],
            $ttl,
            static fn () => AdminSidebar::navigation(),
        );
    }
}
