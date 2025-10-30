<?php

namespace App\Core\Settings;

use App\Core\Cache\InvalidationService;
use App\Core\Cache\Keys;
use App\Core\Settings\Events\SettingsUpdated;
use App\Core\Settings\Models\CompanySetting;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Şirket ayarlarını tip güvenli biçimde okuma/yazma ve önbellekleme görevini üstlenir.
 * Tüm okuma istekleri tenant bazlı anahtarlarla cache edilir ve stale-while-revalidate
 * politikasıyla hızlı cevap verilir.
 */
class SettingsRepository
{
    private const STALE_SECONDS = 120;

    public function __construct(
        private readonly CacheRepository $cache,
        private readonly InvalidationService $cacheInvalidation,
        private readonly Dispatcher $events,
    ) {
    }

    public function get(int $companyId, string $key, mixed $default = null): mixed
    {
        $payload = $this->readFromCache($companyId, $key);

        if ($payload === null) {
            $payload = $this->revalidate($companyId, $key);
        }

        if ($payload === null || $payload['exists'] === false) {
            return $default;
        }

        return $this->castValue($payload['value'], $payload['type']);
    }

    public function getBool(int $companyId, string $key, bool $default = false): bool
    {
        $value = $this->get($companyId, $key, $default);

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function getInt(int $companyId, string $key, int $default = 0): int
    {
        $value = $this->get($companyId, $key, $default);

        return (int) $value;
    }

    public function getJson(int $companyId, string $key, array $default = []): array
    {
        $value = $this->get($companyId, $key, json_encode($default));

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : $default;
    }

    /**
     * Birden fazla anahtarı tek seferde döndürür.
     *
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    public function many(int $companyId, array $keys, array $defaults = []): array
    {
        $results = [];

        foreach ($keys as $key) {
            $results[$key] = $this->get($companyId, $key, $defaults[$key] ?? null);
        }

        return $results;
    }

    public function set(int $companyId, string $key, mixed $value, string $type, ?int $updatedBy = null): void
    {
        [$normalized, $type] = $this->normalizeValue($value, $type);

        CompanySetting::query()->updateOrCreate(
            [
                'company_id' => $companyId,
                'key' => $key,
            ],
            [
                'type' => $type,
                'value' => $normalized,
                'updated_by' => $updatedBy,
            ]
        );

        $this->storeInCache($companyId, $key, $type, $normalized, true);

        $this->cacheInvalidation->flushTenant($companyId, ['settings', 'menu'], [
            'reason' => 'settings.updated',
            'key' => $key,
        ]);

        $this->events->dispatch(new SettingsUpdated($companyId, [$key], $updatedBy));
    }

    public function setMany(int $companyId, array $items, ?int $updatedBy = null): void
    {
        $changedKeys = [];

        foreach ($items as $key => $definition) {
            $value = $definition['value'] ?? null;
            $type = $definition['type'] ?? 'string';

            [$normalized, $resolvedType] = $this->normalizeValue($value, $type);

            CompanySetting::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'key' => $key,
                ],
                [
                    'type' => $resolvedType,
                    'value' => $normalized,
                    'updated_by' => $updatedBy,
                ]
            );

            $this->storeInCache($companyId, $key, $resolvedType, $normalized, true);

            $changedKeys[] = $key;
        }

        if ($changedKeys !== []) {
            $this->cacheInvalidation->flushTenant($companyId, ['settings', 'menu'], [
                'reason' => 'settings.updated.bulk',
                'keys' => $changedKeys,
            ]);

            $this->events->dispatch(new SettingsUpdated($companyId, $changedKeys, $updatedBy));
        }
    }

    private function readFromCache(int $companyId, string $key): ?array
    {
        $cacheKey = $this->cacheKey($companyId, $key);
        $payload = $this->cache->get($cacheKey);

        if (! is_array($payload)) {
            return null;
        }

        $softExpiry = isset($payload['soft_expiry']) ? CarbonImmutable::parse($payload['soft_expiry']) : null;
        if ($softExpiry && now()->greaterThan($softExpiry)) {
            $this->revalidate($companyId, $key, $payload);
        }

        return $payload;
    }

    private function revalidate(int $companyId, string $key, ?array $currentPayload = null): ?array
    {
        $record = CompanySetting::query()
            ->where('company_id', $companyId)
            ->where('key', $key)
            ->first();

        $payload = $record
            ? [
                'exists' => true,
                'type' => $record->type,
                'value' => $record->value,
            ]
            : [
                'exists' => false,
                'type' => 'string',
                'value' => null,
            ];

        $this->storeInCache($companyId, $key, $payload['type'], $payload['value'], $payload['exists']);

        return $payload;
    }

    private function storeInCache(int $companyId, string $key, string $type, mixed $value, bool $exists): void
    {
        $cacheKey = $this->cacheKey($companyId, $key);
        $ttl = (int) config('cache.ttl_profiles.warm', 900);
        $payload = [
            'exists' => $exists,
            'type' => $type,
            'value' => $value,
            'soft_expiry' => now()->addSeconds($ttl)->toIso8601String(),
        ];

        $this->cache->put($cacheKey, $payload, $ttl + self::STALE_SECONDS);
        $this->cacheInvalidation->attachKeyToTags($cacheKey, $this->cacheTags($companyId), $ttl + self::STALE_SECONDS);
    }

    private function cacheKey(int $companyId, string $key): string
    {
        $normalized = Str::of($key)->replace('.', ':')->replace(['/', ' '], ':')->lower()->toString();

        return Keys::forTenant($companyId, ['settings', $normalized], 'v1');
    }

    private function cacheTags(int $companyId): array
    {
        return [sprintf('tenant:%d', $companyId), 'settings'];
    }

    private function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'int', 'integer' => (int) $value,
            'json' => is_array($value) ? $value : json_decode((string) $value, true) ?? [],
            'email', 'string' => $value,
            default => $value,
        };
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function normalizeValue(mixed $value, string $type): array
    {
        $type = strtolower($type);

        return match ($type) {
            'bool', 'boolean' => [(filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0'), 'bool'],
            'int', 'integer' => [strval((int) $value), 'int'],
            'json' => [$this->normalizeJson($value), 'json'],
            'email' => [$this->normalizeEmail($value), 'email'],
            'string' => [strval($value ?? ''), 'string'],
            default => throw new InvalidArgumentException(sprintf('Desteklenmeyen ayar tipi: %s', $type)),
        };
    }

    private function normalizeJson(mixed $value): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        throw new InvalidArgumentException('JSON tipindeki ayarlar için dizi veya geçerli JSON beklenir.');
    }

    private function normalizeEmail(mixed $value): string
    {
        $email = is_string($value) ? trim($value) : '';

        if ($email === '') {
            return '';
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Geçerli bir e-posta adresi girilmelidir.');
        }

        return strtolower($email);
    }
}
