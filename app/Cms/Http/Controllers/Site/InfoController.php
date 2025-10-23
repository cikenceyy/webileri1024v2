<?php

namespace App\Cms\Http\Controllers\Site;

use App\Cms\Support\CmsRepository;
use App\Cms\Support\Seo;
use Illuminate\Routing\Controller;

class InfoController extends Controller
{
    public function __construct(protected CmsRepository $repository, protected Seo $seo)
    {
    }

    public function kvkk()
    {
        return $this->render('tr');
    }

    public function kvkkEn()
    {
        return $this->render('en');
    }

    protected function render(string $locale)
    {
        $data = $this->repository->read('kvkk', $locale);
        $seo = $this->seo->for('kvkk', [], $locale);

        return view('cms::site.kvkk', [
            'locale' => $locale,
            'data' => $data,
            'seo' => $seo,
            'scripts' => $this->repository->scripts('kvkk', $locale),
        ]);
    }
}
