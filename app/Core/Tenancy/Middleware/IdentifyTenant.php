<?php

namespace App\Core\Tenancy\Middleware;

use App\Core\Support\Models\Company;
use App\Core\Support\Models\CompanyDomain;
use App\Core\Tenancy\Support\DomainNormalizer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gelen host bilgisinden şirketi tespit eder, cache kullanımını loglar ve teşhis verilerini paylaşır.
 */
class IdentifyTenant
{
    private const CACHE_PREFIX = 'tenant:domain:';
    private const CACHE_MISS = '__domain_miss__';

    public function handle(Request $request, Closure $next): Response
    {
        $originalHost = (string) $request->getHost();
        $resolution = $this->resolveCompanyFromRequest($request, $originalHost);
        $company = $resolution['company'] ?? null;

        if (! $company && ! config('tenancy.cloud_enabled', false)) {
            $company = $this->resolveLocalFallback();
            $resolution['source'] = 'local_fallback';
            $resolution['fallback_used'] = true;
            $resolution['company'] = $company;
        }

        if (! $company) {
            $this->logResolution($originalHost, null, $resolution, 'not-found');
            abort(404, 'Tenant bulunamadı.');
        }

        app()->instance('company', $company);
        view()->share('company', $company);

        $request->attributes->set('company_id', $company->id);
        $request->attributes->set('company', $company);
        $request->attributes->set('tenant_diagnostics', $resolution);
        app()->instance('tenant.diagnostics', $resolution);

        if (
            class_exists(\Spatie\Permission\PermissionRegistrar::class)
            && app()->bound(\Spatie\Permission\PermissionRegistrar::class)
        ) {
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        }

        $this->logResolution($originalHost, $company->id, $resolution, 'resolved');

        return $next($request);
    }

    /**
     * @return array{
     *     company: ?Company,
     *     domain_id: ?int,
     *     host: string,
     *     normalized_host: string,
     *     cache_hit: bool,
     *     cache_key: ?string,
     *     source: string,
     *     fallback_used?: bool,
     *     is_primary?: bool,
     *     legacy?: bool,
     *     ttl?: int,
     *     hosts_tried: array<int, string>
     * }
     */
    private function resolveCompanyFromRequest(Request $request, string $originalHost): array
    {
        $normalizedHost = DomainNormalizer::normalize($originalHost);
        $ttl = max(0, (int) config('tenancy.domain_cache_ttl', 300));
        $hosts = DomainNormalizer::candidates($normalizedHost, config('tenancy.strip_www', true));

        $result = [
            'company' => null,
            'domain_id' => null,
            'host' => $originalHost,
            'normalized_host' => $normalizedHost,
            'cache_hit' => false,
            'cache_key' => null,
            'source' => 'database',
            'ttl' => $ttl,
            'hosts_tried' => $hosts,
        ];

        foreach ($hosts as $candidate) {
            $cacheKey = self::CACHE_PREFIX . md5($candidate);
            $result['cache_key'] = $cacheKey;

            $payload = $ttl > 0
                ? $this->rememberDomain($cacheKey, $candidate, $ttl, $result['cache_hit'])
                : $this->lookupDomain($candidate);

            if ($payload === self::CACHE_MISS || $payload === null) {
                continue;
            }

            $company = Company::query()->find($payload['company_id']);

            if (! $company) {
                continue;
            }

            $result['company'] = $company;
            $result['domain_id'] = $payload['domain_id'];
            $result['is_primary'] = $payload['is_primary'];
            $result['source'] = $result['cache_hit'] ? 'cache' : 'database';
            $result['legacy'] = $payload['legacy'] ?? false;

            return $result;
        }

        return $result;
    }

    private function rememberDomain(string $cacheKey, string $host, int $ttl, bool &$hit): mixed
    {
        $cached = Cache::get($cacheKey, null);

        if ($cached !== null) {
            $hit = true;

            return $cached;
        }

        $hit = false;
        $resolved = $this->lookupDomain($host) ?? self::CACHE_MISS;
        Cache::put($cacheKey, $resolved, $ttl);

        return $resolved;
    }

    private function lookupDomain(string $host): ?array
    {
        $domain = CompanyDomain::query()
            ->where('host', $host)
            ->first(['id', 'company_id', 'is_primary']);

        if ($domain) {
            return [
                'company_id' => (int) $domain->company_id,
                'domain_id' => (int) $domain->id,
                'is_primary' => (bool) $domain->is_primary,
            ];
        }

        $company = Company::query()->where('domain', $host)->first(['id']);

        if ($company) {
            return [
                'company_id' => (int) $company->id,
                'domain_id' => null,
                'is_primary' => true,
                'legacy' => true,
            ];
        }

        return null;
    }

    private function resolveLocalFallback(): ?Company
    {
        $fallbackId = (int) (config('tenancy.local_fallback_company_id') ?? 0);

        if ($fallbackId > 0) {
            $company = Company::query()->find($fallbackId);
            if ($company) {
                return $company;
            }
        }

        return null;
    }

    private function logResolution(string $host, ?int $companyId, array $resolution, string $status): void
    {
        $context = [
            'host' => $host,
            'normalized_host' => $resolution['normalized_host'] ?? '',
            'hosts_tried' => $resolution['hosts_tried'] ?? [],
            'company_id' => $companyId,
            'domain_id' => $resolution['domain_id'] ?? null,
            'cache_hit' => $resolution['cache_hit'] ?? false,
            'source' => $resolution['source'] ?? 'unknown',
            'fallback_used' => $resolution['fallback_used'] ?? false,
            'ttl' => $resolution['ttl'] ?? null,
            'cache_key' => $resolution['cache_key'] ?? null,
        ];

        try {
            Log::channel('cache')->info('tenancy.resolve.' . $status, $context);
        } catch (\Throwable) {
            // Log kanalı devre dışı olabilir; sessiz geç.
        }
    }

    /**
     * @deprecated Domain cache anahtarları IdentifyTenant üzerinden üretildiği için kullanılmaya devam eder.
     */
    public static function cacheKeyFor(string $host): string
    {
        $normalized = DomainNormalizer::normalize($host);

        return self::CACHE_PREFIX . md5($normalized);
    }

    /**
     * @deprecated Yeni DomainCacheManager sınıfı tercih edilmelidir.
     */
    public static function flushCacheForDomains(string ...$domains): void
    {
        $ttl = max(0, (int) config('tenancy.domain_cache_ttl', 300));

        if ($ttl <= 0) {
            return;
        }

        foreach ($domains as $domain) {
            foreach (DomainNormalizer::candidates($domain, config('tenancy.strip_www', true)) as $host) {
                Cache::forget(self::cacheKeyFor($host));
            }
        }
    }
}
