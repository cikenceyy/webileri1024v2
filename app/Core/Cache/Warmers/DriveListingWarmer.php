<?php

namespace App\Core\Cache\Warmers;

use App\Core\Cache\InvalidationService;
use App\Core\Cache\Keys;
use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Drive\Support\DriveStructure;

/**
 * Drive modülündeki sayfa özetleri ve ilk sayfa listelerini sıcak tutar.
 */
class DriveListingWarmer
{
    public function __construct(private readonly InvalidationService $cache)
    {
    }

    public function warm(int $companyId): void
    {
        $warmTtl = (int) config('cache.ttl_profiles.warm', 900);
        $hotTtl = (int) config('cache.ttl_profiles.hot', 60);
        $tenantTag = sprintf('tenant:%d', $companyId);

        $this->cache->rememberWithTags(
            Keys::forTenant($companyId, ['drive', 'stats'], 'v1'),
            [$tenantTag, 'drive', 'drive:stats'],
            $warmTtl,
            fn () => $this->buildStats($companyId),
        );

        $this->cache->rememberWithTags(
            Keys::forTenant($companyId, ['drive', 'list', 'recent'], 'v1'),
            [$tenantTag, 'drive', 'drive:recent'],
            $hotTtl,
            fn () => $this->recentMediaPayload($companyId),
        );
    }

    private function buildStats(int $companyId): array
    {
        $base = Media::query()->where('company_id', $companyId);
        $documentCategories = Media::documentCategories();
        $mediaCategories = Media::mediaCategories();

        $stats = [
            'recent_documents' => [
                'total' => (clone $base)->whereIn('category', $documentCategories)->count(),
                'important' => (clone $base)->whereIn('category', $documentCategories)->where('is_important', true)->count(),
            ],
            'recent_media' => [
                'total' => (clone $base)->whereIn('category', $mediaCategories)->count(),
                'important' => (clone $base)->whereIn('category', $mediaCategories)->where('is_important', true)->count(),
            ],
        ];

        foreach (DriveStructure::folders() as $folder) {
            $stats['folder_' . $folder['key']] = [
                'total' => (clone $base)->where('category', $folder['key'])->count(),
                'important' => (clone $base)->where('category', $folder['key'])->where('is_important', true)->count(),
            ];
        }

        foreach (Media::moduleKeys() as $module) {
            $stats['module_' . $module] = [
                'total' => (clone $base)->where('module', $module)->count(),
                'important' => (clone $base)->where('module', $module)->where('is_important', true)->count(),
            ];

            foreach (DriveStructure::moduleFolderDefinitions($module) as $folder) {
                $key = 'module_' . $module . '__' . $folder['key'];
                $stats[$key] = [
                    'total' => (clone $base)
                        ->where('module', $module)
                        ->where('category', $folder['key'])
                        ->count(),
                    'important' => (clone $base)
                        ->where('module', $module)
                        ->where('category', $folder['key'])
                        ->where('is_important', true)
                        ->count(),
                ];
            }
        }

        return $stats;
    }

    private function recentMediaPayload(int $companyId): array
    {
        return Media::query()
            ->select(['id', 'company_id', 'category', 'module', 'original_name', 'ext', 'mime', 'size', 'created_at'])
            ->where('company_id', $companyId)
            ->latest('created_at')
            ->limit(12)
            ->get()
            ->map(fn (Media $media) => [
                'id' => $media->id,
                'category' => $media->category,
                'module' => $media->module,
                'name' => $media->original_name,
                'ext' => $media->ext,
                'mime' => $media->mime,
                'size' => $media->size,
                'created_at' => optional($media->created_at)->toIso8601String(),
            ])
            ->all();
    }
}
