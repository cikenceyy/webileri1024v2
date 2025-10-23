<?php

namespace App\Cms\Support\Front\Providers;

use App\Cms\Support\CmsRepository;

class CatalogProvider
{
    public function __construct(protected CmsRepository $repository)
    {
    }

    public function list(array $options = []): array
    {
        $locale = $options['locale'] ?? $this->repository->locale();
        $limit = $options['limit'] ?? null;

        $data = $this->repository->read('catalogs', $locale);
        $items = $data['blocks']['list'] ?? [];

        if (empty($items)) {
            $items = $this->stubCatalogs($locale);
        }

        $items = array_values($items);

        if ($limit !== null) {
            $items = array_slice($items, 0, (int) $limit);
        }

        return $items;
    }

    protected function stubCatalogs(string $locale): array
    {
        $fallbackLocale = 'tr';
        $stubs = [
            [
                'title' => [
                    'tr' => 'Genel Ürün Çözümleri',
                    'en' => 'General Product Solutions',
                ],
                'cover' => null,
                'file' => null,
            ],
            [
                'title' => [
                    'tr' => 'Otomasyon Hatları',
                    'en' => 'Automation Lines',
                ],
                'cover' => null,
                'file' => null,
            ],
            [
                'title' => [
                    'tr' => 'Servis ve Bakım Paketleri',
                    'en' => 'Service & Maintenance Packages',
                ],
                'cover' => null,
                'file' => null,
            ],
        ];

        return array_map(static function (array $item) use ($locale, $fallbackLocale) {
            return [
                'title' => $item['title'][$locale] ?? $item['title'][$fallbackLocale],
                'cover' => $item['cover'],
                'file' => $item['file'],
            ];
        }, $stubs);
    }
}
