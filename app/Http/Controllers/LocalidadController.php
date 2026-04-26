<?php

namespace App\Http\Controllers;

use App\Services\EventifyApiService;

class LocalidadController extends Controller
{
    public function __construct(private EventifyApiService $api) {}

    public function index()
    {
        $resp        = $this->api->localidades();
        $localidades = $resp['data'] ?? $resp;

        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'ItemList',
            'name'            => 'Localidades con comercios en Eventify',
            'itemListElement' => collect($localidades)->map(fn($loc, $i) => [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $loc['nombre'] ?? $loc['name'] ?? '',
                'url'      => url('/localidades/' . ($loc['slug'] ?? '')),
            ])->values()->all(),
        ];

        return view('localidades.index', [
            'title'       => 'Localidades con comercios adheridos a Eventify',
            'description' => 'Descubre los comercios locales adheridos a Eventify en tu municipio. Encuentra restaurantes, bares, peluquerías y más.',
            'canonical'   => url('/localidades'),
            'schema'      => $schema,
            'localidades' => $localidades,
        ]);
    }

    public function show(string $slug)
    {
        $response  = $this->api->localidad($slug);
        $localidad = $response['data'] ?? $response;
        $comercios = $localidad['comercios'] ?? [];
        $nombre    = $localidad['nombre'] ?? $localidad['name'] ?? ucfirst($slug);

        $breadcrumb = [
            ['label' => 'Inicio',      'url' => '/'],
            ['label' => 'Localidades', 'url' => '/localidades'],
            ['label' => $nombre,       'url' => "/localidades/{$slug}"],
        ];

        $faqs = $this->faqsLocalidad($nombre);

        $schema = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'           => 'ItemList',
                    'name'            => "Comercios en {$nombre}",
                    'itemListElement' => collect($comercios)->take(10)->map(fn($c, $i) => [
                        '@type'    => 'ListItem',
                        'position' => $i + 1,
                        'name'     => $c['nombre'] ?? '',
                        'url'      => url('/localidades/' . $slug),
                    ])->values()->all(),
                ],
                $faqs,
            ],
        ];

        return view('localidades.show', [
            'title'       => "Comercios en {$nombre} — Eventify",
            'description' => "Encuentra los mejores comercios locales en {$nombre} con fidelización QR. Restaurantes, bares, peluquerías y más.",
            'canonical'   => url("/localidades/{$slug}"),
            'schema'      => $schema,
            'localidad'   => $localidad,
            'comercios'   => $comercios,
            'breadcrumb'  => $breadcrumb,
            'nombre'      => $nombre,
        ]);
    }

    public function showConCategoria(string $loc, string $cat)
    {
        $responseLoc = $this->api->localidad($loc);
        $localidad   = $responseLoc['data'] ?? $responseLoc;
        $nombreLoc   = $localidad['nombre'] ?? $localidad['name'] ?? ucfirst($loc);

        $responseComercio = $this->api->comercios(['localidad' => $loc, 'categoria' => $cat]);
        $lista            = $responseComercio['data'] ?? $responseComercio;

        $categorias = $this->api->categorias();
        $categoria  = collect($categorias['data'] ?? $categorias)->firstWhere('slug', $cat);
        $nombreCat  = $categoria['nombre'] ?? $categoria['name'] ?? ucfirst($cat);

        $breadcrumb = [
            ['label' => 'Inicio',      'url' => '/'],
            ['label' => 'Localidades', 'url' => '/localidades'],
            ['label' => $nombreLoc,    'url' => "/localidades/{$loc}"],
            ['label' => $nombreCat,    'url' => "/localidades/{$loc}/{$cat}"],
        ];

        $schema = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'           => 'ItemList',
                    'name'            => "{$nombreCat} en {$nombreLoc}",
                    'itemListElement' => collect($lista)->take(10)->map(fn($c, $i) => [
                        '@type'    => 'ListItem',
                        'position' => $i + 1,
                        'name'     => $c['nombre'] ?? '',
                    ])->values()->all(),
                ],
                $this->faqsLocalidad($nombreLoc, $nombreCat),
            ],
        ];

        return view('localidades.show', [
            'title'       => "{$nombreCat} en {$nombreLoc} — Eventify",
            'description' => "Los mejores {$nombreCat} en {$nombreLoc} con programa de fidelización. Encuentra ofertas y vuelve a tus favoritos.",
            'canonical'   => url("/localidades/{$loc}/{$cat}"),
            'schema'      => $schema,
            'localidad'   => $localidad,
            'comercios'   => $lista,
            'breadcrumb'  => $breadcrumb,
            'nombre'      => "{$nombreCat} en {$nombreLoc}",
            'categoriaActiva' => $nombreCat,
        ]);
    }

    private function faqsLocalidad(string $loc, string $cat = ''): array
    {
        $tipo = $cat ?: 'comercios';
        return [
            '@type'      => 'FAQPage',
            'mainEntity' => [
                ['@type' => 'Question', 'name' => "¿Qué {$tipo} hay en {$loc} con Eventify?", 'acceptedAnswer' => ['@type' => 'Answer', 'text' => "En {$loc} encontrarás {$tipo} adheridos a Eventify que ofrecen fidelización QR. Escanea su código, regístrate y recibe sus ofertas y novedades."]],
                ['@type' => 'Question', 'name' => '¿Es gratuito para los clientes?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Sí, totalmente gratuito. Solo necesitas escanear el QR con tu móvil.']],
            ],
        ];
    }
}
