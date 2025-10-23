<?php

namespace App\Cms\Http\Controllers\Site;

use App\Cms\Support\CmsRepository;
use App\Cms\Support\Front\Providers\CatalogProvider;
use App\Cms\Support\Seo;
use Illuminate\Routing\Controller;

class CatalogController extends Controller
{
    public function __construct(
        protected CmsRepository $repository,
        protected Seo $seo,
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
        $data = $this->repository->read('catalogs', $locale);
        $seo = $this->seo->for('catalogs', [], $locale);

        return view('cms::site.catalogs', [
            'locale' => $locale,
            'data' => $data,
            'seo' => $seo,
            'catalogs' => $this->catalogs->list([
                'locale' => $locale,
            ]),
            'scripts' => $this->repository->scripts('catalogs', $locale),
        ]);
    }
}
