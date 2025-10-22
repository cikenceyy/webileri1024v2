<?php

use App\Cms\Http\Controllers\Site\CatalogController;
use App\Cms\Http\Controllers\Site\ContactController;
use App\Cms\Http\Controllers\Site\CorporateController;
use App\Cms\Http\Controllers\Site\HomeController;
use App\Cms\Http\Controllers\Site\InfoController;
use App\Cms\Http\Controllers\Site\ProductController;
use App\Cms\Http\Controllers\Site\ProductShowController;
use App\Cms\Support\SitemapGenerator;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/sitemap.xml', function (SitemapGenerator $generator) {
        return $generator->generate();
    })->name('cms.sitemap');

    Route::get('/robots.txt', function () {
        $content = app()->environment('production') ? 'User-agent: *\nAllow: /' : 'User-agent: *\nDisallow: /';
        return response($content, 200, ['Content-Type' => 'text/plain']);
    });

    Route::get('/', [HomeController::class, 'index'])->name('cms.home');
    Route::get('/kurumsal', [CorporateController::class, 'index'])->name('cms.corporate');
    Route::get('/iletisim', [ContactController::class, 'index'])->name('cms.contact');
    Route::post('/iletisim', [ContactController::class, 'submit'])->name('cms.contact.submit');
    Route::get('/bilgi/kvkk', [InfoController::class, 'kvkk'])->name('cms.kvkk');
    Route::get('/kataloglar', [CatalogController::class, 'index'])->name('cms.catalogs');
    Route::get('/urunler', [ProductController::class, 'index'])->name('cms.products');
    Route::get('/urun/{slug}', [ProductShowController::class, 'show'])->name('cms.product.show');

    Route::prefix('en')->name('cms.en.')->group(function () {
        Route::get('/', [HomeController::class, 'indexEn'])->name('home');
        Route::get('/corporate', [CorporateController::class, 'indexEn'])->name('corporate');
        Route::get('/contact', [ContactController::class, 'indexEn'])->name('contact');
        Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
        Route::get('/info/kvkk', [InfoController::class, 'kvkkEn'])->name('kvkk');
        Route::get('/catalogs', [CatalogController::class, 'indexEn'])->name('catalogs');
        Route::get('/products', [ProductController::class, 'indexEn'])->name('products');
        Route::get('/product/{slug}', [ProductShowController::class, 'showEn'])->name('product.show');
    });
});
