<?php

namespace App\Http\Controllers;

use App\Models\Articulo;

class BlogController extends Controller
{
    public function index()
    {
        $articulos = Articulo::publicados()
            ->orderByDesc('fecha_publicacion')
            ->paginate(12);

        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'Blog',
            'name'     => 'Blog de Eventify',
            'url'      => url('/blog'),
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
        $articulo = Articulo::publicados()->where('slug', $slug)->firstOrFail();

        $schemaType = $articulo->schema_type ?? 'BlogPosting';
        $image      = $articulo->og_image ?? $articulo->imagen_principal;

        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => $schemaType,
            'headline'        => $articulo->titulo,
            'description'     => $articulo->extracto,
            'datePublished'   => optional($articulo->fecha_publicacion)->toIso8601String(),
            'dateModified'    => $articulo->updated_at->toIso8601String(),
            'author'          => ['@type' => 'Person', 'name' => $articulo->autor ?? 'Eventify'],
            'publisher'       => ['@type' => 'Organization', 'name' => 'Eventify', 'url' => url('/')],
            'image'           => $image,
            'url'             => url("/blog/{$slug}"),
        ];

        return view('blog.show', [
            'title'       => $articulo->meta_title ?? $articulo->titulo,
            'description' => $articulo->meta_description ?? $articulo->extracto,
            'canonical'   => $articulo->canonical ?? url("/blog/{$slug}"),
            'indexable'   => $articulo->indexable,
            'schema'      => $schema,
            'ogImage'     => $image,
            'articulo'    => $articulo,
        ]);
    }
}
