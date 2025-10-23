<?php

namespace App\Cms\Http\Controllers\Site;

use App\Cms\Support\CmsRepository;
use App\Cms\Support\Front\Providers\ProductProvider;
use App\Cms\Support\Seo;
use Illuminate\Routing\Controller;

class ProductShowController extends Controller
{
    public function __construct(
        protected CmsRepository $repository,
        protected Seo $seo,
        protected ProductProvider $products,
    )
    {
    }

    public function show(string $slug)
    {
        return $this->render('tr', $slug);
    }

    public function showEn(string $slug)
    {
        return $this->render('en', $slug);
    }

    protected function render(string $locale, string $slug)
    {
        $product = $this->products->detail([
            'slug' => $slug,
            'locale' => $locale,
        ]);
        abort_unless($product, 404);

        $seo = $this->seo->for('product_show', [
            'title' => $product['name'] ?? null,
            'description' => $product['short_desc'] ?? null,
            'og_image' => $product['cover_image'] ?? null,
        ], $locale, [
            'slug' => $slug,
            'product' => $product,
        ]);

        return view('cms::site.product_show', [
            'locale' => $locale,
            'product' => $product,
            'seo' => $seo,
            'scripts' => $this->repository->scripts('product_show', $locale),
            'data' => $this->repository->read('product_show', $locale),
        ]);
    }
}
