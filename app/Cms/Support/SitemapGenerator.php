<?php

namespace App\Cms\Support;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class SitemapGenerator
{
    public function __construct(protected CmsRepository $repository)
    {
    }

    public function generate(): Response
    {
        $locale = $this->repository->locale();
        $pages = [
            '',
            'kurumsal',
            'iletisim',
            'bilgi/kvkk',
            'kataloglar',
            'urunler',
        ];

        $urls = [];
        foreach ($pages as $path) {
            $urls[] = $this->urlForPath($path);
            $urls[] = $this->urlForPath(Str::start($path ? 'en/' . $path : 'en', '/'));
        }

        foreach ($this->repository->allProducts('tr') as $product) {
            $urls[] = $this->urlForPath('urun/' . $product['slug']);
        }

        foreach ($this->repository->allProducts('en') as $product) {
            $urls[] = $this->urlForPath('en/product/' . $product['slug']);
        }

        $body = view('cms::site.sitemap', ['urls' => array_unique($urls)])->render();

        return new Response($body, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    protected function urlForPath(string $path): string
    {
        return URL::to('/' . ltrim($path, '/'));
    }
}
