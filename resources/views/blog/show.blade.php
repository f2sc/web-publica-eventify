@extends('layouts.app')

@section('content')

<article class="article-wrapper">
    <header class="article-header">
        <div class="container">
            <x-breadcrumb :items="[
                ['label' => 'Inicio', 'url' => '/'],
                ['label' => 'Blog',   'url' => '/blog'],
                ['label' => $articulo->titulo, 'url' => '/blog/' . $articulo->slug],
            ]" />

            @if($articulo->categoria_blog)
            <span class="blog-category">{{ $articulo->categoria_blog }}</span>
            @endif

            <h1 class="article-title">{{ $articulo->titulo }}</h1>

            <div class="article-meta">
                @if($articulo->autor)
                <span>Por <strong>{{ $articulo->autor }}</strong></span>
                @endif
                @if($articulo->fecha_publicacion)
                <time datetime="{{ $articulo->fecha_publicacion->toDateString() }}">
                    {{ $articulo->fecha_publicacion->format('d \d\e F \d\e Y') }}
                </time>
                @endif
            </div>
        </div>
    </header>

    @if($articulo->imagen_principal)
    <div class="article-featured-img container">
        <img src="{{ $articulo->imagen_principal }}"
             alt="{{ $articulo->titulo }}"
             width="800" height="450"
             loading="eager">
    </div>
    @endif

    <div class="container">
        <div class="article-body">
            {!! nl2br(e($articulo->contenido)) !!}
        </div>

        @if(count($articulo->etiquetasArray()) > 0)
        <div class="article-tags">
            @foreach($articulo->etiquetasArray() as $tag)
            <span class="tag">{{ $tag }}</span>
            @endforeach
        </div>
        @endif
    </div>
</article>

<section class="section" style="background:#f9fafb">
    <div class="container" style="text-align:center">
        <h2 style="font-size:1.4rem;margin-bottom:1rem">¿Tienes un comercio local?</h2>
        <p style="color:#6b7280;margin-bottom:1.5rem">Prueba Eventify gratis y empieza a fidelizar a tus clientes hoy mismo.</p>
        <a href="{{ $appUrl }}/register?source=blog-articulo" class="btn btn-primary">Registrarme gratis</a>
    </div>
</section>

@endsection

@push('head')
<style>
.article-wrapper { max-width: 100%; }
.article-header { background: #f9fafb; padding: 2.5rem 0 2rem; border-bottom: 1px solid #e5e7eb; }
.blog-category { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6c3fc5; display: block; margin: 0.75rem 0 0.5rem; }
.article-title { font-size: clamp(1.5rem, 3vw, 2.2rem); font-weight: 800; line-height: 1.25; margin-bottom: 1rem; }
.article-meta { display: flex; gap: 1.5rem; font-size: 0.875rem; color: #6b7280; flex-wrap: wrap; }
.article-featured-img { margin: 2rem auto; }
.article-featured-img img { width: 100%; max-height: 450px; object-fit: cover; border-radius: 12px; }
.article-body { max-width: 740px; margin: 2.5rem auto; font-size: 1.05rem; line-height: 1.8; color: #374151; }
.article-body p { margin-bottom: 1.25rem; }
.article-tags { max-width: 740px; margin: 0 auto 3rem; display: flex; flex-wrap: wrap; gap: 0.5rem; }
.tag { background: #e8deff; color: #6c3fc5; font-size: 0.8rem; padding: 0.25rem 0.75rem; border-radius: 99px; }
</style>
@endpush
