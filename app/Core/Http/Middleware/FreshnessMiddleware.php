<?php

namespace App\Core\Http\Middleware;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * JSON/CSV GET uçlarında ETag & Last-Modified başlıklarını üreterek 304 dönüşünü sağlar.
 */
class FreshnessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $request->isMethod('GET')) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type');
        if (! str_contains($contentType, 'application/json') && ! str_contains($contentType, 'text/csv')) {
            return $response;
        }

        $content = $response->getContent();
        $etag = 'W/"' . sha1((string) $content) . '"';
        $response->setEtag($etag);

        $timestamp = $response->headers->get('X-Freshness-Timestamp')
            ? CarbonImmutable::parse($response->headers->get('X-Freshness-Timestamp'))
            : CarbonImmutable::now();

        $response->setLastModified($timestamp);

        if ($request->headers->get('If-None-Match') === $etag
            || $this->matchesLastModified($request, $timestamp)) {
            $response->setStatusCode(304);
            $response->setContent('');
        }

        return $response;
    }

    private function matchesLastModified(Request $request, CarbonImmutable $timestamp): bool
    {
        $header = $request->headers->get('If-Modified-Since');
        if (! $header) {
            return false;
        }

        try {
            $client = CarbonImmutable::parse($header);
        } catch (\Throwable $exception) {
            return false;
        }

        return $client->greaterThanOrEqualTo($timestamp);
    }
}
