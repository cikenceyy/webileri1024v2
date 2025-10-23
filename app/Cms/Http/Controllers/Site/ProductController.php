<?php

namespace App\Cms\Http\Controllers\Site;

use App\Cms\Support\CmsRepository;
use App\Cms\Support\Front\Providers\ProductProvider;
use App\Cms\Support\Seo;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    public function __construct(
        protected CmsRepository $repository,
        protected Seo $seo,
        protected ProductProvider $products,
    )
    {
    }

    public function index()
    {
        return $this->render('tr');
    }

    public function indexEn()
    {
        return $this->render('en');
    }

    protected function render(string $locale)
    {
        $data = $this->repository->read('products', $locale);
        $seo = $this->seo->for('products', [], $locale);

        return view('cms::site.products', [
            'locale' => $locale,
            'products' => $this->products->list([
                'locale' => $locale,
            ]),
            'seo' => $seo,
            'scripts' => $this->repository->scripts('products', $locale),
            'data' => $data,
        ]);
    }
}
