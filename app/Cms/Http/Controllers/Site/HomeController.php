<?php

namespace App\Cms\Http\Controllers\Site;

use App\Cms\Support\CmsRepository;
use App\Cms\Support\Seo;
use Illuminate\Routing\Controller;

class HomeController extends Controller
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
        $data = $this->repository->read('home', $locale);
        $seo = $this->seo->for('home', [], $locale);

        return view('site.home', [
            'locale' => $locale,
            'data' => $data,
            'seo' => $seo,
            'featuredProducts' => $this->repository->featuredProducts(6, $locale),
            'catalogs' => $this->repository->read('catalogs', $locale)['blocks']['list'] ?? [],
            'scripts' => $this->repository->scripts('home', $locale),
        ]);
    }
}
