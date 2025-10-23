<?php

namespace App\Cms\Http\Controllers\Site;

use App\Cms\Support\CmsRepository;
use App\Cms\Support\Seo;
use Illuminate\Routing\Controller;

class CatalogController extends Controller
{
    public function __construct(protected CmsRepository $repository, protected Seo $seo)
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

        return view('site.catalogs', [
            'locale' => $locale,
            'data' => $data,
            'seo' => $seo,
            'catalogs' => $data['blocks']['list'] ?? [],
            'scripts' => $this->repository->scripts('catalogs', $locale),
        ]);
    }
}
