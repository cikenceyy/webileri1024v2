<?php

namespace App\Modules\Settings\Domain;

use App\Core\Contracts\SettingsReader;
use App\Modules\Settings\Domain\Events\SettingsUpdated;
use App\Modules\Settings\Domain\Models\Setting;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\DatabaseManager;

class SettingsService implements SettingsReader
{
    public function __construct(
        protected CacheRepository $cache,
        protected DatabaseManager $database,
    ) {
    }

    public function get(int $companyId): SettingsDTO
    {
        $cachedVersion = $this->cache->get($this->versionKey($companyId));

        if ($cachedVersion) {
            $cached = $this->cache->get($this->cacheKey($companyId, (int) $cachedVersion));
            if ($cached instanceof SettingsDTO) {
                return $cached;
            }
        }

        $model = Setting::query()->where('company_id', $companyId)->first();

        $dto = $model ? SettingsDTO::fromArray($model->data ?? []) : SettingsDTO::defaults();
        $version = $model?->version ?? 1;

        $this->cache->forever($this->versionKey($companyId), $version);
        $this->cache->forever($this->cacheKey($companyId, $version), $dto);

        return $dto;
    }

    public function getDefaults(int $companyId): array
    {
        return $this->get($companyId)->defaultsSection();
    }

    public function update(int $companyId, SettingsDTO $dto, int $userId): SettingsDTO
    {
        return $this->database->connection()->transaction(function () use ($companyId, $dto, $userId) {
            $model = Setting::query()->lockForUpdate()->firstOrNew(['company_id' => $companyId]);

            $previousVersion = $model->exists ? (int) $model->version : 0;

            $model->fill([
                'data' => $dto->toArray(),
                'version' => $previousVersion + 1,
                'updated_by' => $userId,
            ]);

            if (! $model->exists) {
                $model->company_id = $companyId;
            }

            $model->save();

            $this->cache->forget($this->cacheKey($companyId, max($previousVersion, 1)));
            $this->cache->forever($this->versionKey($companyId), $model->version);
            $this->cache->forever($this->cacheKey($companyId, (int) $model->version), $dto);

            event(new SettingsUpdated(
                companyId: $companyId,
                updatedBy: $userId,
                version: (int) $model->version,
                settings: $dto,
            ));

            return $dto;
        });
    }

    public function version(int $companyId): int
    {
        $version = $this->cache->get($this->versionKey($companyId));

        if ($version) {
            return (int) $version;
        }

        $model = Setting::query()->select('version')->where('company_id', $companyId)->first();

        $resolved = $model?->version ?? 1;
        $this->cache->forever($this->versionKey($companyId), $resolved);

        return (int) $resolved;
    }

    protected function cacheKey(int $companyId, int $version): string
    {
        return sprintf('settings:%d:v%d', $companyId, $version);
    }

    protected function versionKey(int $companyId): string
    {
        return sprintf('settings:%d:version', $companyId);
    }
}
