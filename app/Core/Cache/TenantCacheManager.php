<?php

namespace App\Core\Cache;

use App\Core\Cache\Warmers\DashboardSummaryWarmer;
use App\Core\Cache\Warmers\DriveListingWarmer;
use App\Core\Cache\Warmers\MenuWarmer;
use App\Core\Cache\Warmers\SidebarWarmer;
use Illuminate\Support\Arr;

/**
 * Tenant başına önbellek ısıtma ve temizlik operasyonlarını orkestra eder.
 */
class TenantCacheManager
{
    /** @var array<string, object> */
    private array $warmers;

    public function __construct(
        private readonly InvalidationService $invalidation,
        private readonly CacheEventLogger $logger,
        MenuWarmer $menuWarmer,
        SidebarWarmer $sidebarWarmer,
        DashboardSummaryWarmer $dashboardSummaryWarmer,
        DriveListingWarmer $driveListingWarmer,
    ) {
        $this->warmers = [
            'menu' => $menuWarmer,
            'sidebar' => $sidebarWarmer,
            'dashboard' => $dashboardSummaryWarmer,
            'drive' => $driveListingWarmer,
        ];
    }

    /**
     * Seçilen varlıklar için ısıtma çalıştırır ve log kaydı düşer.
     *
     * @param  array<int, string>  $entities
     * @return array<int, string>
     */
    public function warmTenant(int $companyId, array $entities = []): array
    {
        $entities = $this->normalizeEntities($entities);
        $executed = [];

        foreach ($entities as $entity) {
            $warmer = $this->warmers[$entity] ?? null;

            if (! $warmer || ! method_exists($warmer, 'warm')) {
                continue;
            }

            $warmer->warm($companyId);
            $executed[] = $entity;
        }

        if ($executed !== []) {
            $this->logger->record('warm', $companyId, [
                'entities' => $executed,
                'store' => config('cache.default'),
            ]);
        }

        return $executed;
    }

    /**
     * Tenant için tag bazlı temizlik çalıştırır. Hard seçeneği tüm bilinen tagleri ekler.
     *
     * @param  array<int, string>  $tags
     */
    public function flushTenant(int $companyId, array $tags = [], bool $hard = false, array $context = []): void
    {
        $tags = $this->normalizeTags($tags);

        if ($hard) {
            $tags = array_unique(array_merge($tags, ['menu', 'sidebar', 'dashboard', 'drive', 'settings', 'permissions']));
            $context['mode'] = 'hard';
        }

        $this->invalidation->flushTenant($companyId, $tags, $context);
    }

    /**
     * Tenant bilgisi olmadan tag bazlı temizlik (global flush) yapar.
     *
     * @param  array<int, string>  $tags
     */
    public function flushByTags(array $tags, ?int $companyId = null, array $context = []): void
    {
        $this->invalidation->flushTags($this->normalizeTags($tags), $companyId, $context);
    }

    /**
     * @param  array<int, string>  $entities
     * @return array<int, string>
     */
    private function normalizeEntities(array $entities): array
    {
        $entities = Arr::map($entities, fn ($entity) => strtolower((string) $entity));

        if ($entities === [] || in_array('*', $entities, true)) {
            return array_keys($this->warmers);
        }

        return array_values(array_intersect(array_keys($this->warmers), $entities));
    }

    /**
     * @param  array<int, string>  $tags
     * @return array<int, string>
     */
    private function normalizeTags(array $tags): array
    {
        $tags = Arr::map($tags, fn ($tag) => strtolower(trim((string) $tag)));
        $tags = array_filter($tags, fn ($tag) => $tag !== '');

        return array_values(array_unique($tags));
    }
}
