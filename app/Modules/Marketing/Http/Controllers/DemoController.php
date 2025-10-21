<?php

namespace App\Modules\Marketing\Http\Controllers;

use Illuminate\Contracts\View\View;

class DemoController
{
    public function index(): View
    {
        return view('marketing::demo');
    }
}
