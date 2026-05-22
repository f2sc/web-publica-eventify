<?php

namespace App\Http\Controllers;

use App\Services\EventifyApiService;

class CategoriaController extends Controller
{
    public function __construct(private EventifyApiService $api) {}

    public function show(string $slug)
    {
        $comercios = $this->api->comercios(['categoria' => $slug]);
        $lista     = collect($comercios['data'] ?? $comercios)
            ->filter(fn($c) => (bool)($c['mostrar_en_web'] ?? true))
            ->values()->all();

        $categorias = $this->api->categorias();
        $categoria  = collect($categorias['data'] ?? $categorias)->firstWhere('slug', $slug);
        $nombre     = $categoria['nombre'] ?? $categoria['name'] ?? ucfirst($slug);

        $breadcrumb = [
            ['label' => 'Inicio',    'url' => '/'],
            ['label' => $nombre,     'url' => "/categorias/{$slug}"],
        ];

        $schema = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'           => 'ItemList',
                    'name'            => "{$nombre} en Eventify",
                    'itemListElement' => collect($lista)->take(10)->map(fn($c, $i) => [
                        '@type'    => 'ListItem',
                        'position' => $i + 1,
                        'name'     => $c['nombre'] ?? '',
                    ])->values()->all(),
                ],
                [
                    '@type'      => 'FAQPage',
                    'mainEntity' => [
                        ['@type' => 'Question', 'name' => "¿Qué {$nombre} están en Eventify?", 'acceptedAnswer' => ['@type' => 'Answer', 'text' => "Puedes encontrar {$nombre} de distintas localidades adheridos a Eventify con programas de fidelización QR."]],
                    ],
                ],
            ],
        ];

        return view('categorias.show', [
            'title'       => "{$nombre} con fidelización QR — Eventify",
            'description' => "Descubre los mejores {$nombre} con programa de fidelización Eventify. Escanea el QR, recibe ofertas y vuelve.",
            'canonical'   => url("/categorias/{$slug}"),
            'schema'      => $schema,
            'comercios'   => $lista,
            'breadcrumb'  => $breadcrumb,
            'nombre'      => $nombre,
            'slug'        => $slug,
        ]);
    }
}
