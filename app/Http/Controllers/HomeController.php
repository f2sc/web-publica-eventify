<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Services\EventifyApiService;

class HomeController extends Controller
{
    public function __construct(private EventifyApiService $api) {}

    public function index()
    {
        try {
            $resp        = $this->api->localidades();
            $localidades = collect($resp['data'] ?? $resp)->take(8)->values()->all();
        } catch (\Throwable $e) {
            report($e);
            $localidades = [];
        }

        try {
            $respAsoc     = $this->api->asociaciones();
            $asociaciones = collect($respAsoc['data'] ?? $respAsoc)->take(8)->values()->all();
        } catch (\Throwable $e) {
            report($e);
            $asociaciones = [];
        }

        try {
            $respCom  = $this->api->comercios();
            $comercios = collect($respCom['data'] ?? $respCom)
                ->filter(fn($c) => !empty($c['nombre_comercial']) && !empty($c['localidad']['nombre']) && (bool)($c['mostrar_en_web'] ?? true))
                ->shuffle()
                ->take(3)
                ->values()
                ->all();
        } catch (\Throwable $e) {
            report($e);
            $comercios = [];
        }

        try {
            $statsResp = $this->api->stats();
            $stats     = $statsResp['data'] ?? $statsResp;
        } catch (\Throwable $e) {
            report($e);
            $stats = [];
        }

        $articulos = Articulo::publicados()
            ->with('categoriaBlog')
            ->orderByDesc('fecha_publicacion')
            ->take(4)
            ->get();

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
            'title'        => 'Fidelización QR para el comercio local',
            'description'  => 'Eventify conecta comercios locales con sus clientes mediante QR, notificaciones push y ofertas. Únete gratis.',
            'canonical'    => url('/'),
            'schema'       => $schema,
            'localidades'  => $localidades,
            'asociaciones' => $asociaciones,
            'comercios'    => $comercios,
            'stats'        => $stats,
            'articulos'    => $articulos,
        ]);
    }

    public function comoFunciona()
    {
        try {
            $respCom     = $this->api->comercios();
            $comercioDemo = collect($respCom['data'] ?? $respCom)
                ->filter(fn($c) => !empty($c['nombre_comercial']) && !empty($c['localidad']['nombre']) && (bool)($c['mostrar_en_web'] ?? true))
                ->shuffle()
                ->first();
        } catch (\Throwable $e) {
            report($e);
            $comercioDemo = null;
        }

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
            'title'        => 'Cómo funciona Eventify',
            'description'  => 'Descubre cómo Eventify conecta comercios locales con sus clientes en 3 sencillos pasos: QR, registro y notificaciones.',
            'canonical'    => url('/como-funciona'),
            'schema'       => $schema,
            'comercioDemo' => $comercioDemo,
        ]);
    }

    public function paraComercios()
    {
        try {
            $statsResp = $this->api->stats();
            $stats     = $statsResp['data'] ?? $statsResp;
        } catch (\Throwable $e) {
            report($e);
            $stats = [];
        }

        try {
            $respCom   = $this->api->comercios();
            $comercios = collect($respCom['data'] ?? $respCom)
                ->filter(fn($c) => !empty($c['nombre_comercial']) && (bool)($c['mostrar_en_web'] ?? true))
                ->values()->all();
        } catch (\Throwable $e) {
            report($e);
            $comercios = [];
        }

        return view('para-comercios', [
            'title'      => 'Eventify para comercios — Fideliza a tus clientes con QR',
            'description'=> 'Consigue que tus clientes vuelvan con notificaciones push, ofertas y fidelización QR. Sin app, sin complicaciones.',
            'canonical'  => url('/para-comercios'),
            'stats'      => $stats,
            'comercios'  => $comercios,
        ]);
    }

    public function paraAsociaciones()
    {
        try {
            $resp     = $this->api->asociaciones();
            $slugs    = collect($resp['data'] ?? $resp)->pluck('slug')->take(8)->all();
            $asociaciones = collect($slugs)->map(function ($slug) {
                try {
                    $detail = $this->api->asociacion($slug);
                    $asoc   = $detail['data'] ?? $detail;
                    $asoc['comercios'] = collect($asoc['comercios'] ?? [])
                        ->filter(fn($c) => (bool)($c['mostrar_en_web'] ?? true))
                        ->values()->all();
                    return $asoc;
                } catch (\Throwable $e) {
                    return null;
                }
            })->filter(fn($a) => $a !== null && count($a['comercios'] ?? []) > 0)
              ->values()->all();
        } catch (\Throwable $e) {
            report($e);
            $asociaciones = [];
        }

        try {
            $statsResp = $this->api->stats();
            $stats     = $statsResp['data'] ?? $statsResp;
        } catch (\Throwable $e) {
            report($e);
            $stats = [];
        }

        return view('para-asociaciones', [
            'title'        => 'Eventify para asociaciones de comerciantes',
            'description'  => 'Digitaliza el comercio de tu barrio o municipio con Eventify. Herramienta colectiva de fidelización para asociaciones y ayuntamientos.',
            'canonical'    => url('/para-asociaciones'),
            'asociaciones' => $asociaciones,
            'stats'        => $stats,
        ]);
    }

    public function privacidad()
    {
        return view('legal.privacidad', [
            'title'       => 'Política de privacidad — Eventify',
            'description' => 'Política de privacidad de Eventify. Cómo recogemos, usamos y protegemos tus datos personales.',
            'canonical'   => url('/privacidad'),
            'indexable'   => false,
        ]);
    }

    public function terminos()
    {
        return view('legal.terminos', [
            'title'       => 'Términos y condiciones — Eventify',
            'description' => 'Términos y condiciones de uso de la plataforma Eventify.',
            'canonical'   => url('/terminos'),
            'indexable'   => false,
        ]);
    }

    public function cookies()
    {
        return view('legal.cookies', [
            'title'       => 'Política de cookies — Eventify',
            'description' => 'Política de cookies de Eventify. Información sobre las cookies que utilizamos.',
            'canonical'   => url('/cookies'),
            'indexable'   => false,
        ]);
    }
}
