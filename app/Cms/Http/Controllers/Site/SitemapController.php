<?php

namespace App\Cms\Http\Controllers\Site;

use App\Cms\Support\SitemapGenerator;
use Illuminate\Routing\Controller;

class SitemapController extends Controller
{
    public function __invoke(SitemapGenerator $generator)
    {
        return $generator->generate();
    }
}
