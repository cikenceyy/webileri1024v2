<?php

namespace App\Cms\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

class SitemapGenerator
{
    public function __construct(protected CmsRepository $repository)
    {
    }

    public function generate(): Response
    {
        $entries = array_merge(
            $this->staticPageEntries(),
            $this->productEntries()
        );

        $normalised = collect($entries)
            ->filter(fn ($entry) => !empty($entry['loc']))
            ->map(function (array $entry) {
                $entry['lastmod'] = $this->formatDate($entry['lastmod'] ?? null);
                $entry['changefreq'] = $entry['changefreq'] ?? 'daily';
                $entry['priority'] = number_format((float) ($entry['priority'] ?? 0.6), 1, '.', '');

                return $entry;
            })
            ->unique('loc')
            ->values()
            ->all();

        $body = view('cms::site.sitemap', ['entries' => $normalised])->render();

        return new Response($body, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    protected function staticPageEntries(): array
    {
        $pages = [
            [
                'key' => 'home',
                'paths' => ['tr' => '/', 'en' => '/en'],
                'priority' => 1.0,
            ],
            [
                'key' => 'corporate',
                'paths' => ['tr' => '/kurumsal', 'en' => '/en/corporate'],
                'priority' => 0.7,
            ],
            [
                'key' => 'contact',
                'paths' => ['tr' => '/iletisim', 'en' => '/en/contact'],
                'priority' => 0.7,
            ],
            [
                'key' => 'kvkk',
                'paths' => ['tr' => '/bilgi/kvkk', 'en' => '/en/info/kvkk'],
                'priority' => 0.6,
            ],
            [
                'key' => 'catalogs',
                'paths' => ['tr' => '/kataloglar', 'en' => '/en/catalogs'],
                'priority' => 0.8,
            ],
            [
                'key' => 'products',
                'paths' => ['tr' => '/urunler', 'en' => '/en/products'],
                'priority' => 0.9,
            ],
        ];

        $entries = [];

        foreach ($pages as $meta) {
            foreach ($meta['paths'] as $locale => $path) {
                if ($path === null) {
                    continue;
                }

                $lastmod = $this->repository->lastModifiedAt($meta['key'], $locale)
                    ?? $this->repository->lastModifiedAt($meta['key'], 'tr');

                $entries[] = [
                    'loc' => $this->absoluteUrl($path),
                    'lastmod' => $lastmod,
                    'changefreq' => 'daily',
                    'priority' => $meta['priority'],
                ];
            }
        }

        return $entries;
    }

    protected function productEntries(): array
    {
        $entries = [];

        foreach ($this->repository->allProducts('tr') as $product) {
            $entries[] = [
                'loc' => $this->absoluteUrl('urun/' . $product['slug']),
                'lastmod' => $product['updated_at'] ?? null,
                'changefreq' => 'daily',
                'priority' => 0.85,
            ];
        }

        foreach ($this->repository->allProducts('en') as $product) {
            $entries[] = [
                'loc' => $this->absoluteUrl('en/product/' . $product['slug']),
                'lastmod' => $product['updated_at'] ?? null,
                'changefreq' => 'daily',
                'priority' => 0.85,
            ];
        }

        return $entries;
    }

    protected function absoluteUrl(string $path): string
    {
        return URL::to('/' . ltrim($path, '/'));
    }

    protected function formatDate(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->toAtomString();
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toAtomString();
        }

        if (is_string($value) && $value !== '') {
            try {
                return Carbon::parse($value)->toAtomString();
            } catch (\Throwable) {
                // fall through to now
            }
        }

        return now()->toAtomString();
    }
}
