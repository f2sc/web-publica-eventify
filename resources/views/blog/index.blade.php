@extends('layouts.app')

@section('content')

<div class="sub-hero">
    <div class="sub-hero-bg" style="background-image:url('{{ asset('images/big/comerciante-tablet-cafeteria-blog-eventify.png') }}');background-position:center 30%;"></div>
    <div class="sub-hero-ov"></div>
    <div class="sub-hero-cnt">
        <div class="sub-ey">Blog Eventify</div>
        <h1>Ideas para crecer.<br>Historias que inspiran.</h1>
        <p>Guías, casos de éxito y tendencias para el comercio local que quiere dar el salto digital.</p>
    </div>
</div>

<section class="section" style="background:#fff;">
    <div class="container">

        {{-- Filtros de categoría — generados dinámicamente desde la BD --}}
        @php $categoriasNav = \App\Models\CategoriaBlog::orderBy('nombre')->get(); @endphp
        <div class="blog-filter">
            <a href="{{ url('/blog') }}" class="bpill on">Todos</a>
            @foreach($categoriasNav as $catNav)
            <a href="{{ url('/blog/categoria/' . $catNav->slug) }}" class="bpill">{{ $catNav->nombre }}</a>
            @endforeach
        </div>

        @if($articulos->count() > 0)
            {{-- Artículo destacado (primero de la lista) --}}
            @php $destacado = $articulos->first(); @endphp
            <div class="blog-featured">
                <a href="{{ url('/blog/' . $destacado->slug) }}" class="bf-img">
                    @if($destacado->imagen_principal)
                    <img src="{{ $destacado->imagen_principal }}" alt="{{ $destacado->titulo }}" loading="lazy">
                    @else
                    <img src="{{ asset('images/big/cafeteria-barrio-fidelizacion-clientes.jpg') }}" alt="{{ $destacado->titulo }}" loading="lazy">
                    @endif
                    <div class="bf-img-ov"></div>
                    @if($destacado->categoria_blog)
                    <div class="bf-cat">{{ $destacado->categoria_blog }}</div>
                    @endif
                </a>
                <div class="bf-body">
                    @if($destacado->categoria_blog)
                    <div class="bf-tag">{{ $destacado->categoria_blog }}</div>
                    @endif
                    <h2><a href="{{ url('/blog/' . $destacado->slug) }}" style="color:inherit;text-decoration:none;">{{ $destacado->titulo }}</a></h2>
                    @if($destacado->extracto)
                    <p>{{ Str::limit($destacado->extracto, 180) }}</p>
                    @endif
                    <div class="bf-meta">
                        <div class="bf-avatar">&#x270D;</div>
                        <div>
                            <div class="bf-author">{{ $destacado->autor ?? 'Equipo Eventify' }}</div>
                            @if($destacado->fecha_publicacion)
                            <div class="bf-date">{{ $destacado->fecha_publicacion->format('d M Y') }}</div>
                            @endif
                        </div>
                        <div class="bf-readtime">&#x23F1; {{ $destacado->tiempoLectura() }} min</div>
                    </div>
                </div>
            </div>

            {{-- Grid del resto de artículos --}}
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
                        @if($articulo->categoria_blog)
                        <span class="blog-category">{{ $articulo->categoria_blog }}</span>
                        @endif
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
                                <time datetime="{{ $articulo->fecha_publicacion->toDateString() }}">{{ $articulo->fecha_publicacion->format('d M') }}</time>
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
        <p style="text-align:center;color:#6b7280;padding:4rem 0">Próximamente publicaremos artículos sobre comercio local y fidelización.</p>
        @endif
    </div>
</section>

{{-- NEWSLETTER CTA --}}
<x-newsletter-cta fuente="blog-newsletter" />

@endsection

@push('head')
<style>
.blog-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
.blog-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: box-shadow .2s; }
.blog-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.1); }
.blog-card-img { width: 100%; height: 200px; object-fit: cover; display: block; }
.blog-card-body { padding: 1.25rem; }
.blog-category { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--brand); }
.blog-card-title { font-size: 1.05rem; margin: 0.5rem 0; line-height: 1.4; }
.blog-card-title a { color: var(--navy); text-decoration: none; }
.blog-card-title a:hover { color: var(--brand); }
.blog-card-excerpt { font-size: 0.875rem; color: var(--muted); margin-bottom: 1rem; }
.blog-card-meta { display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #9ca3af; }
.blog-card-meta-right { display: flex; gap: 0.5rem; align-items: center; }
.blog-readtime { background: #f3f4f6; color: #6b7280; padding: 2px 7px; border-radius: 20px; font-size: 0.75rem; white-space: nowrap; }
.bf-readtime { margin-left: auto; font-size: 0.85rem; color: rgba(255,255,255,.7); white-space: nowrap; }
@media(max-width:768px){ .blog-grid { grid-template-columns: 1fr; } }
</style>
@endpush
