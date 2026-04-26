<?php

namespace App\Http\Controllers;

use App\Services\EventifyApiService;

class HomeController extends Controller
{
    public function __construct(private EventifyApiService $api) {}

    public function index()
    {
        $resp        = $this->api->localidades();
        $localidades = collect($resp['data'] ?? $resp)->take(8)->values()->all();

        $schema = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type' => 'WebSite',
                    'name'  => 'Eventify',
                    'url'   => url('/'),
                    'potentialAction' => [
                        '@type'       => 'SearchAction',
                        'target'      => url('/buscar') . '?q={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
                [
                    '@type'       => 'Organization',
                    'name'        => 'Eventify',
                    'url'         => url('/'),
                    'description' => 'Plataforma de fidelización QR para el comercio local.',
                ],
            ],
        ];

        return view('home', [
            'title'       => 'Fidelización QR para el comercio local',
            'description' => 'Eventify conecta comercios locales con sus clientes mediante QR, notificaciones push y ofertas. Únete gratis.',
            'canonical'   => url('/'),
            'schema'      => $schema,
            'localidades' => $localidades,
        ]);
    }

    public function comoFunciona()
    {
        $schema = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => [
                ['@type' => 'Question', 'name' => '¿Cómo funciona el QR de Eventify?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'El cliente escanea el QR del comercio, se registra con su móvil y empieza a recibir notificaciones push con ofertas y novedades del comercio.']],
                ['@type' => 'Question', 'name' => '¿Es gratis para los comercios?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Sí, el registro es gratuito. Eventify ofrece un plan gratuito con funcionalidades básicas para empezar.']],
                ['@type' => 'Question', 'name' => '¿Los clientes necesitan instalar una app?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'No. Los clientes solo necesitan escanear el QR con la cámara de su móvil. Sin descargas.']],
            ],
        ];

        return view('como-funciona', [
            'title'       => 'Cómo funciona Eventify',
            'description' => 'Descubre cómo Eventify conecta comercios locales con sus clientes en 3 sencillos pasos: QR, registro y notificaciones.',
            'canonical'   => url('/como-funciona'),
            'schema'      => $schema,
        ]);
    }

    public function paraComercios()
    {
        return view('para-comercios', [
            'title'       => 'Eventify para comercios — Fideliza a tus clientes con QR',
            'description' => 'Consigue que tus clientes vuelvan con notificaciones push, ofertas y fidelización QR. Sin app, sin complicaciones.',
            'canonical'   => url('/para-comercios'),
        ]);
    }

    public function paraAsociaciones()
    {
        return view('para-asociaciones', [
            'title'       => 'Eventify para asociaciones de comerciantes',
            'description' => 'Digitaliza el comercio de tu barrio o municipio con Eventify. Herramienta colectiva de fidelización para asociaciones y ayuntamientos.',
            'canonical'   => url('/para-asociaciones'),
        ]);
    }
}
