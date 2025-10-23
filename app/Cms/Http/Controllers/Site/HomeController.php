<?php

namespace App\Cms\Http\Controllers\Site;

use App\Cms\Support\CmsRepository;
use App\Cms\Support\Front\Providers\CatalogProvider;
use App\Cms\Support\Front\Providers\ProductProvider;
use App\Cms\Support\Seo;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function __construct(
        protected CmsRepository $repository,
        protected Seo $seo,
        protected ProductProvider $products,
        protected CatalogProvider $catalogs,
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
        $data = $this->repository->read('home', $locale);
        $seo = $this->seo->for('home', [], $locale);

        return view('cms::site.home', [
            'locale' => $locale,
            'data' => $data,
            'seo' => $seo,
            'featuredProducts' => $this->products->list([
                'locale' => $locale,
                'limit' => 6,
                'featured' => true,
            ]),
            'catalogs' => $this->catalogs->list([
                'locale' => $locale,
                'limit' => 6,
            ]),
            'scripts' => $this->repository->scripts('home', $locale),
        ]);
    }
}
