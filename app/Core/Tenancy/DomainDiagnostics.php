<?php

namespace App\Core\Tenancy;

use App\Core\Cache\CacheEventLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use function tenant;

/**
 * Domain çözümleme teşhis verilerini UI tarafına hazırlar.
 */
class DomainDiagnostics
{
    public function __construct(private readonly CacheEventLogger $logger)
    {
    }

    public function forRequest(Request $request): array
    {
        $runtime = App::bound('tenant.diagnostics') ? (array) App::make('tenant.diagnostics') : [];
        $company = tenant();
        $meta = $this->logger->getMeta($company?->id ?? 0);
        $events = $this->recentDomainEvents();

        return [
            'host' => $runtime['host'] ?? $request->getHost(),
            'normalized_host' => $runtime['normalized_host'] ?? null,
            'company' => $company ? [
                'id' => $company->id,
                'name' => $company->name,
            ] : null,
            'domain_id' => $runtime['domain_id'] ?? null,
            'cache_hit' => (bool) ($runtime['cache_hit'] ?? false),
            'cache_key' => $runtime['cache_key'] ?? null,
            'cache_ttl' => $runtime['ttl'] ?? config('tenancy.domain_cache_ttl'),
            'source' => $runtime['source'] ?? 'unknown',
            'hosts_tried' => $runtime['hosts_tried'] ?? [],
            'fallback_used' => (bool) ($runtime['fallback_used'] ?? false),
            'last_domain_flush' => $meta['last_domain_flush'] ?? null,
            'cloud_enabled' => (bool) config('tenancy.cloud_enabled', false),
            'local_fallback_company_id' => config('tenancy.local_fallback_company_id'),
            'events' => $events,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function recentDomainEvents(): array
    {
        return Collection::make($this->logger->getRecentEvents())
            ->filter(fn (array $event) => Str::startsWith($event['action'], 'domain.'))
            ->take(10)
            ->map(fn (array $event) => [
                'action' => $event['action'],
                'company_id' => $event['company_id'],
                'timestamp' => $event['timestamp']->toDateTimeString(),
                'domains' => Arr::get($event, 'context.domains', []),
                'reason' => Arr::get($event, 'context.reason'),
            ])
            ->values()
            ->all();
    }
}
