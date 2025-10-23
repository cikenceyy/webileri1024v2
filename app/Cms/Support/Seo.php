<?php

namespace App\Cms\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;

class Seo
{
    public function __construct(protected CmsRepository $repository, protected PreviewStore $preview)
    {
    }

    public function for(string $page, array $overrides = [], ?string $locale = null, array $context = []): array
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

        $canonical = $this->canonicalUrl($page, $locale, $context);
        $alternates = $this->alternateUrls($page, $context);
        $schema = $this->structuredData($page, $locale, $context, $metaTitle, $metaDescription, $ogImage);

        return array_filter([
            'title' => $metaTitle,
            'description' => $metaDescription,
            'og_image' => $ogImage,
            'canonical' => $canonical,
            'alternates' => $alternates,
            'schema' => $schema,
        ]);
    }

    protected function canonicalUrl(string $page, string $locale, array $context = []): string
    {
        $route = match ($page) {
            'home' => $locale === 'en' ? 'cms.en.home' : 'cms.home',
            'corporate' => $locale === 'en' ? 'cms.en.corporate' : 'cms.corporate',
            'contact' => $locale === 'en' ? 'cms.en.contact' : 'cms.contact',
            'kvkk' => $locale === 'en' ? 'cms.en.kvkk' : 'cms.kvkk',
            'catalogs' => $locale === 'en' ? 'cms.en.catalogs' : 'cms.catalogs',
            'products' => $locale === 'en' ? 'cms.en.products' : 'cms.products',
            'product_show' => $locale === 'en' ? 'cms.en.product.show' : 'cms.product.show',
            default => $locale === 'en' ? 'cms.en.home' : 'cms.home',
        };

        $params = [];
        if ($route === 'cms.product.show' || $route === 'cms.en.product.show') {
            $slug = $context['slug'] ?? Arr::get($context, 'product.slug');
            if ($slug) {
                $params['slug'] = $slug;
            }
        }

        return URL::to(route($route, $params, false));
    }

    protected function alternateUrls(string $page, array $context = []): array
    {
        return [
            'tr' => $this->canonicalUrl($page, 'tr', $context),
            'en' => $this->canonicalUrl($page, 'en', $context),
        ];
    }

    protected function structuredData(
        string $page,
        string $locale,
        array $context,
        ?string $metaTitle,
        ?string $metaDescription,
        ?string $ogImage
    ): array {
        $organization = $this->organizationSchema($locale);
        $website = $this->websiteSchema($metaTitle, $metaDescription);
        $product = [];

        if ($page === 'product_show' && ($productData = $context['product'] ?? null)) {
            $product = $this->productSchema($productData, $metaDescription, $ogImage);
        }

        return array_filter([
            'organization' => $organization,
            'website' => $website,
            'product' => $product,
        ]);
    }

    protected function organizationSchema(string $locale): array
    {
        $contact = $this->repository->read('contact', $locale);
        $coords = Arr::get($contact, 'blocks.coords', []);
        $emails = $this->repository->emails();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => URL::to('/'),
            'logo' => $this->repository->get('home.blocks.hero.image'),
            'contactPoint' => array_filter([
                '@type' => 'ContactPoint',
                'telephone' => $coords['phone'] ?? null,
                'email' => $coords['email'] ?? ($emails['info_email'] ?? null),
                'areaServed' => strtoupper($locale),
                'contactType' => 'customer support',
            ]),
        ]);
    }

    protected function websiteSchema(?string $metaTitle, ?string $metaDescription): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $metaTitle ?? config('app.name'),
            'url' => URL::to('/'),
            'description' => $metaDescription,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => URL::to('/search?q={search_term_string}'),
                'query-input' => 'required name=search_term_string',
            ],
        ]);
    }

    protected function productSchema(array $product, ?string $metaDescription, ?string $ogImage): array
    {
        $images = array_filter(array_merge(
            Arr::wrap($product['cover_image'] ?? null),
            $product['gallery'] ?? []
        ));

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product['name'] ?? null,
            'description' => $metaDescription ?? ($product['short_desc'] ?? null),
            'sku' => $product['sku'] ?? null,
            'image' => $images ?: Arr::wrap($ogImage),
        ]);
    }
}
