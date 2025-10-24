<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotency
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('idempotency.enabled', true) || ! config('features.idempotency.enforced', true)) {
            /** @var Response $response */
            $response = $next($request);

            return $response;
        }

        $key = $this->resolveKey($request);
        if (! $key) {
            abort(400, 'Idempotency key is required.');
        }

        $cacheKey = $this->cacheKey($request, $key);
        $store = $this->cacheStore();

        if ($payload = $store->get($cacheKey)) {
            $response = new Response($payload['content'] ?? '', $payload['status'] ?? 200);
            foreach ($payload['headers'] ?? [] as $name => $values) {
                foreach (Arr::wrap($values) as $value) {
                    $response->headers->set($name, $value, false);
                }
            }

            $response->headers->set('Idempotency-Replayed', 'true');
            $response->headers->set('Idempotency-Key', $key);

            return $response;
        }

        /** @var Response $response */
        $response = $next($request);

        if ($this->shouldStore($response)) {
            $store->put($cacheKey, [
                'status' => $response->getStatusCode(),
                'headers' => $this->storeableHeaders($response),
                'content' => $response->getContent(),
            ], now()->addSeconds((int) config('idempotency.ttl', 600)));

            $response->headers->set('Idempotency-Key', $key);
        }

        return $response;
    }

    protected function resolveKey(Request $request): ?string
    {
        $header = $request->header(config('idempotency.header', 'Idempotency-Key'));

        if ($header) {
            return trim($header);
        }

        $input = $request->input('idempotency_key');

        return $input ? trim((string) $input) : null;
    }

    protected function cacheKey(Request $request, string $key): string
    {
        $company = currentCompanyId();
        $userId = optional($request->user())->getKey();
        $fingerprint = implode('|', [
            $key,
            $company ?? 'global',
            $userId ?? 'guest',
            $request->method(),
            $request->path(),
        ]);

        return 'idempotency:' . sha1($fingerprint);
    }

    protected function cacheStore()
    {
        $store = config('idempotency.cache_store');

        return $store ? Cache::store($store) : Cache::store();
    }

    protected function shouldStore(Response $response): bool
    {
        $status = $response->getStatusCode();

        return $status >= 200 && $status < 400;
    }

    protected function storeableHeaders(Response $response): array
    {
        $excluded = ['set-cookie'];
        $headers = [];

        foreach ($response->headers->all() as $name => $values) {
            if (in_array(strtolower($name), $excluded, true)) {
                continue;
            }

            $headers[$name] = $values;
        }

        return $headers;
    }
}
