@extends('layouts.app')

@section('content')

<div class="sub-hero">
    <div class="sub-hero-bg" style="background-image:url('{{ $categoria->og_image ?? asset('images/big/comerciante-tablet-cafeteria-blog-eventify.png') }}');background-position:center 30%;"></div>
    <div class="sub-hero-ov"></div>
    <div class="sub-hero-cnt">
        <div class="sub-ey">Blog Eventify · Categoría</div>
        <h1>{{ $categoria->nombre }}</h1>
        @if($categoria->descripcion)
        <p>{{ $categoria->descripcion }}</p>
        @endif
    </div>
</div>

<section class="section" style="background:#fff;">
    <div class="container">

        {{-- Filtros de categoría --}}
        @php
            $todasCategorias = \App\Models\CategoriaBlog::orderBy('nombre')->get();
        @endphp
        <div class="blog-filter">
            <a href="{{ url('/blog') }}" class="bpill">Todos</a>
            @foreach($todasCategorias as $cat)
            <a href="{{ url('/blog/categoria/' . $cat->slug) }}"
               class="bpill {{ $cat->id === $categoria->id ? 'on' : '' }}">
                {{ $cat->nombre }}
            </a>
            @endforeach
        </div>

        @if($articulos->count() > 0)

            {{-- Artículo destacado --}}
            @php $destacado = $articulos->first(); @endphp
            <div class="blog-featured">
                <a href="{{ url('/blog/' . $destacado->slug) }}" class="bf-img">
                    @if($destacado->imagen_principal)
                    <img src="{{ $destacado->imagen_principal }}" alt="{{ $destacado->titulo }}" loading="lazy">
                    @else
                    <img src="{{ asset('images/big/cafeteria-barrio-fidelizacion-clientes.jpg') }}" alt="{{ $destacado->titulo }}" loading="lazy">
                    @endif
                    <div class="bf-img-ov"></div>
                    <div class="bf-cat">{{ $categoria->nombre }}</div>
                </a>
                <div class="bf-body">
                    <div class="bf-tag">{{ $categoria->nombre }}</div>
                    <h2><a href="{{ url('/blog/' . $destacado->slug) }}" style="color:inherit;text-decoration:none;">{{ $destacado->titulo }}</a></h2>
                    @if($destacado->extracto)
                    <p>{{ Str::limit($destacado->extracto, 180) }}</p>
                    @endif
                    <div class="bf-meta">
                        <div class="bf-avatar">&#x270D;</div>
                        <div>
                            <div class="bf-author">{{ $destacado->autor ?? 'Equipo Eventify' }}</div>
                            @if($destacado->fecha_publicacion)
                            <div class="bf-date">{{ $destacado->fecha_publicacion->translatedFormat('d M Y') }}</div>
                            @endif
                        </div>
                        <div class="bf-readtime">&#x23F1; {{ $destacado->tiempoLectura() }} min</div>
                    </div>
                </div>
            </div>

            {{-- Grid del resto --}}
            @if($articulos->count() > 1)
            <div class="blog-grid">
                @foreach($articulos->skip(1) as $articulo)
                <article class="blog-card">
                    @if($articulo->imagen_principal)
                    <a href="{{ url('/blog/' . $articulo->slug) }}">
                        <img src="{{ $articulo->imagen_principal }}" alt="{{ $articulo->titulo }}" loading="lazy" class="blog-card-img">
                    </a>
                    @endif
                    <div class="blog-card-body">
                        <span class="blog-category">{{ $categoria->nombre }}</span>
                        <h2 class="blog-card-title">
                            <a href="{{ url('/blog/' . $articulo->slug) }}">{{ $articulo->titulo }}</a>
                        </h2>
                        @if($articulo->extracto)
                        <p class="blog-card-excerpt">{{ Str::limit($articulo->extracto, 120) }}</p>
                        @endif
                        <div class="blog-card-meta">
                            <span>{{ $articulo->autor ?? 'Equipo Eventify' }}</span>
                            <span class="blog-card-meta-right">
                                @if($articulo->fecha_publicacion)
                                <time datetime="{{ $articulo->fecha_publicacion->toDateString() }}">{{ $articulo->fecha_publicacion->translatedFormat('d M') }}</time>
                                @endif
                                <span class="blog-readtime">{{ $articulo->tiempoLectura() }} min</span>
                            </span>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
            @endif

            <div style="margin-top:3rem;">{{ $articulos->links() }}</div>

        @else
        <p style="text-align:center;color:#6b7280;padding:4rem 0">
            Próximamente publicaremos artículos en esta categoría.
        </p>
        @endif

        {{-- Texto de contexto SEO — visible para Google y LLMs --}}
        @if($categoria->descripcion)
        <div class="cat-seo-block">
            <h2>Sobre la categoría «{{ $categoria->nombre }}»</h2>
            <p>{{ $categoria->descripcion }}</p>
        </div>
        @endif

    </div>
</section>

@endsection

@push('head')
<style>
.cat-seo-block {
    margin-top: 4rem;
    padding: 2rem;
    background: #f9fafb;
    border-radius: 12px;
    border-left: 4px solid var(--brand);
}
.cat-seo-block h2 {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: .75rem;
}
.cat-seo-block p {
    color: var(--muted);
    font-size: .95rem;
    line-height: 1.65;
    margin: 0;
}

/* Reutilizamos los estilos del blog/index */
.blog-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
.blog-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: box-shadow .2s; }
.blog-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.1); }
.blog-card-img { width: 100%; height: 200px; object-fit: cover; display: block; }
.blog-card-body { padding: 1.25rem; }
.blog-category { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--brand); }
.blog-card-title { font-size: 1.05rem; margin: .5rem 0; line-height: 1.4; }
.blog-card-title a { color: var(--navy); text-decoration: none; }
.blog-card-title a:hover { color: var(--brand); }
.blog-card-excerpt { font-size: .875rem; color: var(--muted); margin-bottom: 1rem; }
.blog-card-meta { display: flex; justify-content: space-between; align-items: center; font-size: .8rem; color: #9ca3af; }
.blog-card-meta-right { display: flex; gap: .5rem; align-items: center; }
.blog-readtime { background: #f3f4f6; color: #6b7280; padding: 2px 7px; border-radius: 20px; font-size: .75rem; white-space: nowrap; }
.bf-readtime { margin-left: auto; font-size: .85rem; color: rgba(255,255,255,.7); white-space: nowrap; }
@media(max-width:768px){ .blog-grid { grid-template-columns: 1fr; } }
</style>
@endpush
