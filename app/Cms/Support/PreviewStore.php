<?php

namespace App\Cms\Support;

use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PreviewStore
{
    protected const CACHE_TTL = 3600; // 1 hour

    protected ?string $token = null;

    public function __construct(protected CacheRepository $cache, protected Request $request)
    {
        $this->token = $this->resolveToken();
    }

    public function token(): ?string
    {
        return $this->token;
    }

    public function issueToken(): string
    {
        if ($this->token) {
            return $this->token;
        }

        $token = Str::uuid()->toString();
        $this->request->session()->put('cms.preview.token', $token);
        $this->token = $token;

        return $token;
    }

    public function put(string $token, string $page, string $locale, array $payload): void
    {
        $key = $this->key($token, $page, $locale);
        $this->cache->put($key, $this->sanitizePayload($payload), self::CACHE_TTL);
    }

    public function get(string $token, string $page, string $locale): array
    {
        $key = $this->key($token, $page, $locale);

        return $this->cache->get($key, []);
    }

    public function clear(string $token, string $page, ?string $locale = null): void
    {
        if ($locale) {
            $this->cache->forget($this->key($token, $page, $locale));

            return;
        }

        foreach (['tr', 'en'] as $lang) {
            $this->cache->forget($this->key($token, $page, $lang));
        }
    }

    public function flush(string $token): void
    {
        foreach (Arr::wrap($this->cache->get($this->catalogueKey($token), [])) as $entry) {
            $this->cache->forget($entry);
        }

        $this->cache->forget($this->catalogueKey($token));
    }

    protected function key(string $token, string $page, string $locale): string
    {
        $key = sprintf('cms:preview:%s:%s:%s', $token, $page, $locale);
        $catalogue = $this->cache->get($this->catalogueKey($token), []);
        if (!in_array($key, $catalogue, true)) {
            $catalogue[] = $key;
            $this->cache->put($this->catalogueKey($token), $catalogue, self::CACHE_TTL);
        }

        return $key;
    }

    protected function catalogueKey(string $token): string
    {
        return sprintf('cms:preview:%s:keys', $token);
    }

    protected function resolveToken(): ?string
    {
        // 1. Sıra: Query string
        $token = $this->request->query('preview_token')
            // 2. Sıra: Header
            ?: $this->request->header('X-CMS-Preview-Token')
            // 3. Sıra: Session (varsa!)
            ?: ($this->request->hasSession()
                ? $this->request->session()->get('cms.preview.token')
                : null);

        return $token ? (string) $token : null;
    }

    protected function sanitizePayload(array $payload): array
    {
        return [
            'blocks' => $payload['blocks'] ?? [],
            'seo' => $payload['seo'] ?? [],
            'scripts' => $payload['scripts'] ?? [],
        ];
    }
}
