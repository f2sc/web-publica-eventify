<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\CategoriaBlog;

class BlogController extends Controller
{
    public function index()
    {
        $articulos = Articulo::publicados()
            ->orderByDesc('fecha_publicacion')
            ->paginate(12);

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Blog',
            'name'        => 'Blog de Eventify',
            'url'         => url('/blog'),
            'description' => 'Consejos, tendencias y casos de éxito sobre comercio local y fidelización de clientes.',
        ];

        return view('blog.index', [
            'title'       => 'Blog — Comercio local y fidelización',
            'description' => 'Artículos sobre fidelización de clientes, comercio local y cómo hacer crecer tu negocio con Eventify.',
            'canonical'   => url('/blog'),
            'schema'      => $schema,
            'articulos'   => $articulos,
        ]);
    }

    public function show(string $slug)
    {
        $articulo = Articulo::with('categoriaBlog')->publicados()->where('slug', $slug)->firstOrFail();

        $schemaType = $articulo->schema_type ?? 'BlogPosting';
        $image      = $articulo->og_image ?? $articulo->imagen_principal;

        $schema = [
            '@context'      => 'https://schema.org',
            '@type'         => $schemaType,
            'headline'      => $articulo->titulo,
            'description'   => $articulo->extracto,
            'datePublished' => optional($articulo->fecha_publicacion)->toIso8601String(),
            'dateModified'  => $articulo->updated_at->toIso8601String(),
            'author'        => ['@type' => 'Person', 'name' => $articulo->autor ?? 'Eventify'],
            'publisher'     => ['@type' => 'Organization', 'name' => 'Eventify', 'url' => url('/')],
            'image'         => $image,
            'url'           => url("/blog/{$slug}"),
        ];

        // BreadcrumbList — incluye categoría si existe
        $breadcrumbItems = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Blog', 'item' => url('/blog')],
        ];
        if ($articulo->categoriaBlog) {
            $breadcrumbItems[] = [
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => $articulo->categoriaBlog->nombre,
                'item'     => url('/blog/categoria/' . $articulo->categoriaBlog->slug),
            ];
            $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $articulo->titulo];
        } else {
            $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $articulo->titulo];
        }

        $breadcrumbSchema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $breadcrumbItems,
        ];

        $schemas = [$schema, $breadcrumbSchema];

        // FAQPage schema si el artículo tiene FAQ
        if (!empty($articulo->faq_json) && is_array($articulo->faq_json)) {
            $schemas[] = [
                '@context'   => 'https://schema.org',
                '@type'      => 'FAQPage',
                'mainEntity' => collect($articulo->faq_json)->map(fn ($f) => [
                    '@type'          => 'Question',
                    'name'           => $f['question'] ?? '',
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer'] ?? ''],
                ])->all(),
            ];
        }

        return view('blog.show', [
            'title'            => $articulo->meta_title ?? $articulo->titulo,
            'description'      => $articulo->meta_description ?? $articulo->extracto,
            'canonical'        => $articulo->canonical ?? url("/blog/{$slug}"),
            'indexable'        => $articulo->indexable,
            'schema'           => $schemas,
            'ogImage'          => $image,
            'articulo'         => $articulo,
        ]);
    }

    public function categoria(string $slug)
    {
        $categoria = CategoriaBlog::where('slug', $slug)->firstOrFail();

        $articulos = $categoria->articulosPublicados()->paginate(12);

        $descripcionFallback = "Artículos de la categoría {$categoria->nombre} en el blog de Eventify.";

        // ItemList schema con los artículos de esta categoría
        $itemList = $articulos->map(fn ($a, $i) => [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'url'      => url("/blog/{$a->slug}"),
            'name'     => $a->titulo,
        ])->values()->all();

        $schema = [
            [
                '@context'        => 'https://schema.org',
                '@type'           => 'ItemList',
                'name'            => $categoria->nombre . ' — Blog Eventify',
                'description'     => $categoria->descripcion ?? $descripcionFallback,
                'url'             => url("/blog/categoria/{$slug}"),
                'numberOfItems'   => $articulos->total(),
                'itemListElement' => $itemList,
            ],
            [
                '@context'        => 'https://schema.org',
                '@type'           => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Blog', 'item' => url('/blog')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => $categoria->nombre],
                ],
            ],
        ];

        return view('blog.categoria', [
            'title'       => $categoria->meta_title ?? ($categoria->nombre . ' — Blog Eventify'),
            'description' => $categoria->meta_description ?? ($categoria->descripcion ?? $descripcionFallback),
            'canonical'   => url("/blog/categoria/{$slug}"),
            'schema'      => $schema,
            'ogImage'     => $categoria->og_image ?? null,
            'categoria'   => $categoria,
            'articulos'   => $articulos,
        ]);
    }
}
