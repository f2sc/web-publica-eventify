<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\CategoriaBlog;
use App\Services\EventifyApiService;

class SitemapController extends Controller
{
    public function __construct(private EventifyApiService $api) {}

    public function index()
    {
        $localidades = collect($this->api->localidades()['data'] ?? $this->api->localidades());
        $articulos   = Articulo::publicados()->orderByDesc('fecha_publicacion')->get();
        $categorias  = CategoriaBlog::orderBy('nombre')->get();

        $estaticas = [
            ['url' => url('/'),                 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['url' => url('/como-funciona'),    'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => url('/para-comercios'),   'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => url('/para-asociaciones'),'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => url('/blog'),             'priority' => '0.7', 'changefreq' => 'daily'],
            ['url' => url('/localidades'),      'priority' => '0.7', 'changefreq' => 'weekly'],
        ];

        return response()
            ->view('sitemap', compact('estaticas', 'localidades', 'articulos', 'categorias'))
            ->header('Content-Type', 'application/xml');
    }
}
