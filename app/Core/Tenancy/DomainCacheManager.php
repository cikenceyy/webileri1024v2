<?php

namespace App\Core\Tenancy;

use App\Core\Cache\CacheEventLogger;
use App\Core\Support\Models\Company;
use App\Core\Support\Models\CompanyDomain;
use App\Core\Tenancy\Middleware\IdentifyTenant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Domain önbelleğini tenant bazında yönetir ve olayları loglar.
 */
class DomainCacheManager
{
    public function __construct(private readonly CacheEventLogger $logger)
    {
    }

    /**
     * @param  array<int, string>  $domains
     */
    public function flushForCompany(int $companyId, array $domains = [], array $context = []): void
    {
        $company = Company::query()->find($companyId);

        if (! $company) {
            return;
        }

        $hosts = $domains !== [] ? $domains : $this->collectCompanyHosts($companyId, $company->domain);
        $hosts = array_values(array_unique(array_filter(Arr::map($hosts, 'strval'))));

        if ($hosts !== []) {
            IdentifyTenant::flushCacheForDomains(...$hosts);
        }

        $payload = array_merge($context, [
            'domains' => $hosts,
            'store' => config('cache.default'),
        ]);

        $this->logger->record('domain.flush', $companyId, $payload);

        try {
            Log::channel('cache')->info('tenancy.domain.flush', [
                'company_id' => $companyId,
                'domains' => $hosts,
                'context' => $context,
            ]);
        } catch (\Throwable) {
            // sessizce yutulur, log kanalı kapalı olabilir
        }
    }

    /**
     * @return array<int, string>
     */
    private function collectCompanyHosts(int $companyId, ?string $legacyDomain): array
    {
        $domains = CompanyDomain::query()
            ->where('company_id', $companyId)
            ->pluck('host')
            ->all();

        if ($legacyDomain) {
            $domains[] = $legacyDomain;
        }

        return $domains;
    }
}
