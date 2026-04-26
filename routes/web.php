<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocalidadController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Admin\ArticuloController;
use App\Http\Controllers\Admin\CmsAuthController;
use Illuminate\Support\Facades\Route;

// Páginas públicas
Route::get('/', [HomeController::class, 'index']);
Route::get('/como-funciona', [HomeController::class, 'comoFunciona']);
Route::get('/para-comercios', [HomeController::class, 'paraComercios']);
Route::get('/para-asociaciones', [HomeController::class, 'paraAsociaciones']);

// Blog público
Route::get('/blog', [BlogController::class, 'index']);
Route::get('/blog/{slug}', [BlogController::class, 'show']);

// Directorio
Route::get('/localidades', [LocalidadController::class, 'index']);
Route::get('/localidades/{loc}/{cat}', [LocalidadController::class, 'showConCategoria']);
Route::get('/localidades/{slug}', [LocalidadController::class, 'show']);
Route::get('/categorias/{slug}', [CategoriaController::class, 'show']);

// SEO infrastructure
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Mini-CMS Admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login',  [CmsAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CmsAuthController::class, 'login']);
    Route::post('/logout', [CmsAuthController::class, 'logout'])->middleware('cms.auth')->name('logout');

    Route::middleware('cms.auth')->group(function () {
        Route::resource('articulos', ArticuloController::class);
    });
});
