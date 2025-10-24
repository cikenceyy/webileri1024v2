<?php

namespace App\Cms\Support\Front\Providers;

use App\Cms\Support\CmsRepository;

class ProductProvider
{
    public function __construct(protected CmsRepository $repository)
    {
    }

    public function list(array $options = []): array
    {
        $locale = $options['locale'] ?? $this->repository->locale();
        $limit = $options['limit'] ?? null;
        $featuredOnly = (bool) ($options['featured'] ?? false);

        $products = $this->repository->allProducts($locale);

        if ($featuredOnly) {
            $products = array_filter($products, fn (array $product) => (bool) ($product['is_featured'] ?? false));
        }

        if (empty($products)) {
            $products = $this->stubProducts($locale);

            if ($featuredOnly) {
                $products = array_filter($products, fn (array $product) => (bool) ($product['is_featured'] ?? false));
            }
        }

        $products = array_values($products);

        if ($limit !== null) {
            $products = array_slice($products, 0, (int) $limit);
        }

        return $products;
    }

    public function detail(array $options): ?array
    {
        $locale = $options['locale'] ?? $this->repository->locale();
        $slug = $options['slug'] ?? null;

        if (!$slug) {
            return null;
        }

        $product = $this->repository->findProductBySlug($slug, $locale);

        if ($product) {
            return $product;
        }

        foreach ($this->stubProducts($locale) as $stub) {
            if (($stub['slug'] ?? null) === $slug) {
                return $stub;
            }
        }

        return null;
    }

    protected function stubProducts(string $locale): array
    {
        $stubs = [
            [
                'slug' => 'modular-conveyor',
                'name' => [
                    'tr' => 'Modüler Konveyör Platformu',
                    'en' => 'Modular Conveyor Platform',
                ],
                'short_desc' => [
                    'tr' => 'Uyarlanabilir hatlar için hızlı modül entegrasyonu.',
                    'en' => 'Rapid module integration for adaptive lines.',
                ],
                'sku' => 'MCP-200',
                'cover_image' => null,
                'gallery' => [null, null, null],
                'is_featured' => true,
                'category' => 'assembly',
            ],
            [
                'slug' => 'precision-pick-and-place',
                'name' => [
                    'tr' => 'Hassas Pick & Place Ünitesi',
                    'en' => 'Precision Pick & Place Unit',
                ],
                'short_desc' => [
                    'tr' => 'Hızlı döngülü montajlar için yüksek hassasiyetli robotik başlık.',
                    'en' => 'High-precision robotic head for fast cycle assemblies.',
                ],
                'sku' => 'PPP-480',
                'cover_image' => null,
                'gallery' => [null, null],
                'is_featured' => true,
                'category' => 'robotics',
            ],
            [
                'slug' => 'smart-buffer-system',
                'name' => [
                    'tr' => 'Akıllı Buffer Sistemi',
                    'en' => 'Smart Buffer System',
                ],
                'short_desc' => [
                    'tr' => 'Hat dengesini korumak için enerji verimli ara depolama.',
                    'en' => 'Energy efficient intermediate storage to balance your lines.',
                ],
                'sku' => 'SBS-120',
                'cover_image' => null,
                'gallery' => [null],
                'is_featured' => false,
                'category' => 'intralogistics',
            ],
        ];

        return array_map(function (array $product) use ($locale) {
            $fallbackLocale = 'tr';

            return [
                'slug' => $product['slug'],
                'name' => $product['name'][$locale] ?? $product['name'][$fallbackLocale],
                'short_desc' => $product['short_desc'][$locale] ?? $product['short_desc'][$fallbackLocale],
                'sku' => $product['sku'],
                'cover_image' => $product['cover_image'],
                'gallery' => $product['gallery'],
                'is_featured' => (bool) ($product['is_featured'] ?? false),
                'category' => $product['category'] ?? 'all',
                'updated_at' => now(),
            ];
        }, $stubs);
    }
}
