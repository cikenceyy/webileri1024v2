<?php

namespace App\Modules\_template\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Örnek yönetici controller iskeleti.
 * Kopyaladıktan sonra metotları ve yetki kontrollerini modülün
 * gereksinimlerine göre güncelleyin.
 */
class ExampleController extends Controller
{
    /**
     * Örnek liste sayfasını döndürür.
     */
    public function index(Request $request): Response
    {
        return response()->view('example::admin.index');
    }
}
