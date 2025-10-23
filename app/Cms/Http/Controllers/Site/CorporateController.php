<?php

namespace App\Cms\Http\Controllers\Site;

use App\Cms\Support\CmsRepository;
use App\Cms\Support\Seo;
use Illuminate\Routing\Controller;

class CorporateController extends Controller
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
        $data = $this->repository->read('corporate', $locale);
        $seo = $this->seo->for('corporate', [], $locale);

        return view('cms::site.corporate', [
            'locale' => $locale,
            'data' => $data,
            'seo' => $seo,
            'scripts' => $this->repository->scripts('corporate', $locale),
        ]);
    }
}
