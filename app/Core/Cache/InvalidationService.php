<?php

namespace App\Core\Cache;

use Closure;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Arr;

/**
 * Tenant duyarlı önbellek temizliği ve tag emülasyonu için merkez servis.
 * Redis gibi tag destekleyen sürücülerde native API, file/array gibi sürücülerde
 * ise indeks tablosu kullanarak aynı davranışı sağlar.
 */
class InvalidationService
{
    private ?Repository $repository = null;

    public function __construct(
        private readonly CacheManager $cache,
        private readonly CacheEventLogger $logger,
        private readonly ?string $store = null,
    ) {
    }

    public function flushTenant(int $companyId, array $extraTags = [], array $context = []): void
    {
        $tags = array_merge([sprintf('tenant:%d', $companyId)], $extraTags);
        $this->flushTags($tags, $companyId, array_merge($context, ['scope' => 'tenant']));
    }

    public function flushTags(array $tags, ?int $companyId = null, array $context = []): void
    {
        $tags = array_values(array_unique(array_filter(Arr::flatten($tags), fn ($tag) => $tag !== null && $tag !== '')));

        if ($tags === []) {
            return;
        }

        if ($this->supportsTags()) {
            $this->repository()->tags($tags)->flush();
        } else {
            foreach ($tags as $tag) {
                $this->forgetTagIndex($tag);
            }
        }

        $this->logger->record('flush', $companyId, array_merge($context, [
            'tags' => $tags,
            'store' => $this->storeName(),
        ]));
    }

    public function rememberWithTags(string $key, array $tags, int $ttl, Closure $callback)
    {
        $tags = array_values(array_unique(array_filter(Arr::flatten($tags), fn ($tag) => $tag !== null && $tag !== '')));

        if ($this->supportsTags()) {
            return $this->repository()->tags($tags)->remember($key, $ttl, $callback);
        }

        $value = $this->repository()->remember($key, $ttl, $callback);
        $this->attachKeyToTags($key, $tags, $ttl);

        return $value;
    }

    public function attachKeyToTags(string $key, array $tags, ?int $ttl = null): void
    {
        if ($this->supportsTags()) {
            return;
        }

        $tags = array_values(array_unique(array_filter(Arr::flatten($tags), fn ($tag) => $tag !== null && $tag !== '')));

        if ($tags === []) {
            return;
        }

        $ttl ??= (int) config('cache.ttl_profiles.cold', 86_400);
        $repository = $this->repository();

        foreach ($tags as $tag) {
            $indexKey = $this->tagIndexKey($tag);
            $entries = $repository->get($indexKey, []);
            $entries = is_array($entries) ? $entries : [];

            if (! in_array($key, $entries, true)) {
                $entries[] = $key;
            }

            $repository->put($indexKey, $entries, $ttl);
        }
    }

    public function forget(string $key, array $tags = []): void
    {
        $this->repository()->forget($key);

        if ($this->supportsTags()) {
            return;
        }

        foreach ($tags as $tag) {
            $indexKey = $this->tagIndexKey($tag);
            $entries = $this->repository()->get($indexKey, []);
            if (! is_array($entries)) {
                continue;
            }

            $filtered = array_values(array_filter($entries, fn ($candidate) => $candidate !== $key));

            if ($filtered === []) {
                $this->repository()->forget($indexKey);
            } else {
                $this->repository()->put($indexKey, $filtered, (int) config('cache.ttl_profiles.cold', 86_400));
            }
        }
    }

    private function forgetTagIndex(string $tag): void
    {
        $indexKey = $this->tagIndexKey($tag);
        $repository = $this->repository();

        $entries = $repository->get($indexKey, []);
        $entries = is_array($entries) ? $entries : [];

        foreach ($entries as $key) {
            $repository->forget($key);
        }

        $repository->forget($indexKey);
    }

    private function tagIndexKey(string $tag): string
    {
        return sprintf('tagindex:%s', $tag);
    }

    private function supportsTags(): bool
    {
        $store = $this->repository()->getStore();

        return $store instanceof TaggableStore;
    }

    private function repository(): Repository
    {
        if ($this->repository instanceof Repository) {
            return $this->repository;
        }

        $store = $this->storeName();
        $this->repository = $this->cache->store($store);

        return $this->repository;
    }

    private function storeName(): string
    {
        return $this->store ?: config('cache.default');
    }
}
