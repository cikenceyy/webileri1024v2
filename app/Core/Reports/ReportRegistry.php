<?php

namespace App\Core\Reports;

use App\Core\Cache\Keys;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use RuntimeException;

/**
 * Cold rapor tanımlarını tutar, dirty etiketleri yönetir.
 */
class ReportRegistry
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return [
            [
                'key' => 'inventory.stock-summary',
                'label' => 'Stok Özet Raporu',
                'module' => 'inventory',
                'schedule' => '0 * * * *',
                'ttl' => 3600,
                'depends_on' => ['inventory.stock'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function find(string $key): array
    {
        $definition = collect($this->all())->firstWhere('key', $key);

        if (! $definition) {
            throw new RuntimeException('Tanımlanmamış rapor: ' . $key);
        }

        return $definition;
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    public function shouldGenerate(int $companyId, array $definition): bool
    {
        $ttl = (int) Arr::get($definition, 'ttl', 3600);
        $snapshotFreshKey = Keys::forTenant($companyId, ['reports', 'fresh', $definition['key']]);
        $expiresAt = Cache::store()->get($snapshotFreshKey);

        if ($expiresAt && $expiresAt > now()->timestamp) {
            return $this->hasDirtyTag($companyId, $definition['depends_on'] ?? []);
        }

        return true;
    }

    public function markFresh(int $companyId, string $reportKey, int $ttl): void
    {
        $key = Keys::forTenant($companyId, ['reports', 'fresh', $reportKey]);
        Cache::store()->put($key, now()->addSeconds($ttl)->timestamp, $ttl);
    }

    /**
     * @param  array<int, string>  $tags
     */
    public function markDirty(int $companyId, array $tags): void
    {
        foreach ($tags as $tag) {
            $key = Keys::forTenant($companyId, ['reports', 'dirty', $tag]);
            Cache::store()->put($key, true, Config::get('cache.ttl_profiles.warm', 900));
        }
    }

    /**
     * @param  array<int, string>  $tags
     */
    public function clearDirty(int $companyId, array $tags): void
    {
        foreach ($tags as $tag) {
            $key = Keys::forTenant($companyId, ['reports', 'dirty', $tag]);
            Cache::store()->forget($key);
        }
    }

    /**
     * @param  array<int, string>  $tags
     */
    private function hasDirtyTag(int $companyId, array $tags): bool
    {
        foreach ($tags as $tag) {
            $key = Keys::forTenant($companyId, ['reports', 'dirty', $tag]);
            if (Cache::store()->get($key)) {
                return true;
            }
        }

        return false;
    }
}
