<?php

namespace App\Cms\Http\Controllers\Site;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;

class RobotsController extends Controller
{
    public function __invoke()
    {
        $config = config('cms.robots', []);
        $override = $config['override'] ?? null;

        $indexableEnvironments = $config['index_environments'] ?? ['production'];
        $environment = app()->environment();

        $allowIndex = match ($override) {
            'allow' => true,
            'disallow' => false,
            default => in_array($environment, $indexableEnvironments, true),
        };

        $sitemapUrl = $config['sitemap'] ?? URL::to('/sitemap.xml');

        $content = $allowIndex
            ? "User-agent: *\nAllow: /\nSitemap: {$sitemapUrl}"
            : "User-agent: *\nDisallow: /";

        return Response::make($content, 200, ['Content-Type' => 'text/plain']);
    }
}
