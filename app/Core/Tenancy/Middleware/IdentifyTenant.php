<?php

namespace App\Core\Tenancy\Middleware;

use App\Core\Support\Models\Company;
use App\Core\Support\Models\CompanyDomain;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public const CACHE_PREFIX = 'tenant:domain:';

    public function handle(Request $request, Closure $next): Response
    {

        $company = $this->resolveCompanyFromRequest($request);

        if (! $company && ! config('tenancy.cloud_enabled', false)) {
            $company = $this->resolveLocalFallback();
        }

        if (! $company) {
            abort(404, 'Tenant not found.');
        }

        app()->instance('company', $company);
        view()->share('company', $company);

        $request->attributes->set('company_id', $company->id);
        $request->attributes->set('company', $company);

        if (
            class_exists(\Spatie\Permission\PermissionRegistrar::class)
            && app()->bound(\Spatie\Permission\PermissionRegistrar::class)
        ) {
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        }

        return $next($request);
    }

    private function resolveCompanyFromRequest(Request $request): ?Company
    {
        $host = self::normalizeHost((string) $request->getHost());

        if ($host === '') {
            return null;
        }

        $candidates = $this->candidateHosts($host);

        $company = $this->resolveFromPrimaryDomain($candidates);

        if (! $company) {
            $company = $this->resolveFromDomainAliases($candidates);
        }

        return $company;
    }

        private function resolveFromPrimaryDomain(array $hosts): ?Company
    {
        if ($hosts === []) {
            return null;
        }

        return Company::query()
            ->whereIn('domain', $hosts)
            ->first();
    }

    private function candidateHosts(string $host): array
    {
        $hosts = [];

        if ($host !== '') {
            $hosts[] = $host;
        }

        if (config('tenancy.strip_www', true) && $host !== '') {
            if (Str::startsWith($host, 'www.')) {
                $hosts[] = Str::after($host, 'www.');
            } else {
                $hosts[] = 'www.' . $host;
            }
        }

        return array_values(array_unique(array_filter($hosts)));
    }

    private function resolveFromDomainAliases(array $hosts): ?Company
    {
        if ($hosts === []) {
            return null;
        }

        $ttl = (int) config('tenancy.cache_seconds', 300);

        foreach ($hosts as $candidate) {
            $normalized = self::normalizeHost($candidate);

            if ($normalized === '') {
                continue;
            }

            $cacheKey = self::cacheKeyFor($normalized);

            $companyId = $ttl > 0
                ? Cache::remember($cacheKey, $ttl, fn () => $this->lookupDomainAlias($normalized))
                : $this->lookupDomainAlias($normalized);

            if ($companyId) {
                return Company::find($companyId);
            }
        }

        return null;
    }

    private function lookupDomainAlias(string $host): ?int
    {
        return CompanyDomain::query()
            ->where('domain', $host)
            ->value('company_id');
    }

    private function resolveLocalFallback(): ?Company
    {
        if (app()->environment('production')) {
            return null;
        }

        $fallbackId = (int) (config('tenancy.local_fallback_company_id') ?? 0);

        if ($fallbackId > 0) {
            $company = Company::query()->find($fallbackId);
            if ($company) {
                return $company;
            }
        }

        return Company::query()->first();
    }

    public static function cacheKeyFor(string $host): string
    {
        $normalized = self::normalizeHost($host);

        return self::CACHE_PREFIX . md5($normalized);
    }

    public static function flushCacheForDomains(string ...$domains): void
    {
        $ttl = (int) config('tenancy.cache_seconds', 300);

        if ($ttl <= 0) {
            return;
        }

        foreach ($domains as $domain) {
            $normalized = self::normalizeHost($domain);

            if ($normalized === '') {
                continue;
            }

            foreach (self::candidateHostsForFlush($normalized) as $host) {
                Cache::forget(self::cacheKeyFor($host));
            }
        }
    }

    private static function candidateHostsForFlush(string $host): array
    {
        $hosts = [$host];

        if (config('tenancy.strip_www', true) && $host !== '') {
            if (Str::startsWith($host, 'www.')) {
                $hosts[] = Str::after($host, 'www.');
            } else {
                $hosts[] = 'www.' . $host;
            }
        }

        return array_values(array_unique(array_filter($hosts)));
    }

    private static function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));
        $host = preg_replace('/:\\d+$/', '', $host ?? '');

        return $host ? trim($host) : '';
    }
}
