<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpCacheHeaders
{
    public function handle(Request $request, Closure $next, int $ttl = 60)
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        if (! $request->isMethodSafe() || ! $response instanceof Response) {
            return $response;
        }

        $content = method_exists($response, 'getContent') ? $response->getContent() : null;

        if ($content === null) {
            return $response;
        }

        $etag = 'W/"'.sha1($content).'"';
        $lastModified = gmdate('D, d M Y H:i:s').' GMT';

        if ($request->headers->get('If-None-Match') === $etag) {
            return response()
                ->noContent(Response::HTTP_NOT_MODIFIED)
                ->setCache([
                    'etag' => $etag,
                    'public' => true,
                    'max_age' => $ttl,
                    's_maxage' => $ttl,
                ])
                ->header('Last-Modified', $lastModified);
        }

        $response->setEtag($etag);
        $response->headers->set('Cache-Control', sprintf('public, max-age=%1$d, s-maxage=%1$d, stale-while-revalidate=%1$d', $ttl));
        $response->headers->set('Last-Modified', $lastModified);
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $ttl).' GMT');

        return $response;
    }
}
