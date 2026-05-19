<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocalidadController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SuscriptorController;
use App\Http\Controllers\Admin\ArticuloController;
use App\Http\Controllers\Admin\AiGenerateController;
use App\Http\Controllers\Admin\CategoriaBlogController;
use App\Http\Controllers\Admin\CmsAuthController;
use App\Http\Controllers\Admin\IaConfigController;
use App\Http\Controllers\Admin\IaLogsController;
use Illuminate\Support\Facades\Route;

// Páginas públicas
Route::get('/', [HomeController::class, 'index']);
Route::get('/como-funciona', [HomeController::class, 'comoFunciona']);
Route::get('/para-comercios', [HomeController::class, 'paraComercios']);
Route::get('/para-asociaciones', [HomeController::class, 'paraAsociaciones']);

// Blog público
Route::get('/blog', [BlogController::class, 'index']);
Route::get('/blog/categoria/{slug}', [BlogController::class, 'categoria'])->name('blog.categoria');
Route::get('/blog/{slug}', [BlogController::class, 'show']);

// Directorio
Route::get('/localidades', [LocalidadController::class, 'index']);
Route::get('/localidades/{loc}/{cat}', [LocalidadController::class, 'showConCategoria']);
Route::get('/localidades/{slug}', [LocalidadController::class, 'show']);
Route::get('/categorias/{slug}', [CategoriaController::class, 'show']);

// Páginas legales
Route::get('/privacidad', [HomeController::class, 'privacidad']);
Route::get('/terminos',   [HomeController::class, 'terminos']);
Route::get('/cookies',    [HomeController::class, 'cookies']);

// Newsletter
Route::post('/newsletter/suscribir',      [SuscriptorController::class, 'store']);
Route::get('/newsletter/confirmar/{token}', [SuscriptorController::class, 'confirmar']);
Route::get('/newsletter/cancelar/{token}',  [SuscriptorController::class, 'cancelar']);

// SEO infrastructure
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Mini-CMS Admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login',  [CmsAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CmsAuthController::class, 'login']);
    Route::post('/logout', [CmsAuthController::class, 'logout'])->middleware('cms.auth')->name('logout');

    Route::middleware('cms.auth')->group(function () {
        Route::resource('articulos', ArticuloController::class);
        Route::resource('categorias', CategoriaBlogController::class)->except(['show']);

        // IA — configuración y logs
        Route::prefix('ia')->name('ia.')->group(function () {
            Route::get('config',          [IaConfigController::class, 'edit'])->name('config');
            Route::put('config',          [IaConfigController::class, 'update'])->name('config.update');
            Route::post('test-connection',[IaConfigController::class, 'testConnection'])->name('test');
            Route::post('fetch-models',   [IaConfigController::class, 'fetchModels'])->name('fetch-models');
            Route::get('logs',            [IaLogsController::class, 'index'])->name('logs');
            Route::post('generate',       [AiGenerateController::class, 'generate'])->name('generate');
        });

        // IA — operaciones sobre artículo existente
        Route::prefix('articulos/{articulo}/ai')->name('articulos.ai.')->group(function () {
            Route::post('regenerate', [AiGenerateController::class, 'regenerateField'])->name('regenerate');
            Route::post('image',      [AiGenerateController::class, 'generateImage'])->name('image');
        });
    });
});
