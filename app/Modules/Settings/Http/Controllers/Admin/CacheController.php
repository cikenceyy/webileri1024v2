<?php

namespace App\Modules\Settings\Http\Controllers\Admin;

use App\Core\Cache\CacheEventLogger;
use App\Core\Cache\TenantCacheManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;
use function currentCompanyId;

/**
 * Yönetici tarafında önbellek ısıtma/temizleme aksiyonlarını sunar.
 */
class CacheController extends Controller
{
    public function __construct(
        private readonly TenantCacheManager $manager,
        private readonly CacheEventLogger $logger,
    ) {
    }

    public function index(Request $request): View
    {
        $companyId = currentCompanyId() ?? 0;

        return view('settings::admin.cache', [
            'companyId' => $companyId,
            'cacheStore' => config('cache.default'),
            'cachePrefix' => config('cache.prefix'),
            'meta' => $this->logger->getMeta($companyId),
            'events' => $this->logger->getRecentEvents(),
            'ttlProfiles' => config('cache.ttl_profiles'),
        ]);
    }

    public function warm(Request $request): JsonResponse
    {
        $data = $request->validate([
            'entities' => ['required', 'array', 'min:1'],
            'entities.*' => ['in:menu,sidebar,dashboard,drive'],
        ]);

        $companyId = currentCompanyId() ?? 0;
        $entities = Arr::wrap($data['entities']);

        $executed = $this->manager->warmTenant($companyId, $entities);

        return response()->json([
            'ok' => true,
            'executed' => $executed,
            'message' => __(':count anahtar ısıtıldı.', ['count' => count($executed)]),
            'meta' => $this->serializeMeta($companyId),
            'events' => $this->serializeEvents(),
            'store' => config('cache.default'),
            'prefix' => config('cache.prefix'),
        ]);
    }

    public function flush(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tags' => ['required', 'array', 'min:1'],
            'tags.*' => ['in:menu,sidebar,dashboard,drive,settings,permissions'],
            'hard' => ['sometimes', 'boolean'],
        ]);

        $companyId = currentCompanyId() ?? 0;
        $tags = Arr::wrap($data['tags']);
        $hard = (bool) ($data['hard'] ?? false);

        $this->manager->flushTenant($companyId, $tags, $hard, [
            'reason' => 'settings.cache.ui',
        ]);

        return response()->json([
            'ok' => true,
            'message' => __('Önbellek temizlendi.'),
            'meta' => $this->serializeMeta($companyId),
            'events' => $this->serializeEvents(),
            'store' => config('cache.default'),
            'prefix' => config('cache.prefix'),
        ]);
    }

    private function serializeMeta(int $companyId): array
    {
        $meta = $this->logger->getMeta($companyId);

        return [
            'last_warm' => $meta['last_warm']?->toIso8601String(),
            'last_flush' => $meta['last_flush']?->toIso8601String(),
        ];
    }

    private function serializeEvents(): array
    {
        return array_map(fn (array $event) => [
            'action' => $event['action'],
            'company_id' => $event['company_id'],
            'store' => $event['store'],
            'timestamp' => $event['timestamp']->toIso8601String(),
            'context' => $event['context'],
            'summary' => $this->summarizeEvent($event['action'], $event['context']),
        ], $this->logger->getRecentEvents());
    }

    private function summarizeEvent(string $action, array $context): string
    {
        $tags = $context['tags'] ?? [];
        $tags = is_array($tags) ? implode(', ', $tags) : (string) $tags;

        if ($action === 'warm') {
            $entities = $context['entities'] ?? [];
            $entities = is_array($entities) ? implode(', ', $entities) : (string) $entities;

            return Str::of($entities ?: '---')->upper();
        }

        return Str::of($tags ?: '---')->lower();
    }
}
