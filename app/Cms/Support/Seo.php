<?php

namespace App\Cms\Support;

class Seo
{
    public function __construct(protected CmsRepository $repository)
    {
    }

    public function for(string $page, array $overrides = [], ?string $locale = null): array
    {
        $locale = $locale ?? $this->repository->locale();
        $seo = $this->repository->seo($page, $locale);

        $metaTitle = $overrides['title'] ?? $seo['meta_title'] ?? config('app.name');
        $metaDescription = $overrides['description'] ?? $seo['meta_description'] ?? config('app.description');
        $ogImage = $overrides['og_image'] ?? $seo['og_image'] ?? null;

        if (!$metaTitle && $locale !== 'tr') {
            $fallback = $this->repository->seo($page, 'tr');
            $metaTitle = $fallback['meta_title'] ?? $metaTitle;
            $metaDescription = $fallback['meta_description'] ?? $metaDescription;
            $ogImage = $fallback['og_image'] ?? $ogImage;
        }

        return [
            'title' => $metaTitle,
            'description' => $metaDescription,
            'og_image' => $ogImage,
        ];
    }
}
