@extends('layouts.app')

@section('content')

<section class="hero">
    <div class="hero-container">
        <h1>Blog de Eventify</h1>
        <p>Consejos, tendencias y casos de éxito sobre comercio local y fidelización de clientes.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        @if($articulos->count() > 0)
        <div class="blog-grid">
            @foreach($articulos as $articulo)
            <article class="blog-card">
                @if($articulo->imagen_principal)
                <a href="{{ url('/blog/' . $articulo->slug) }}">
                    <img src="{{ $articulo->imagen_principal }}"
                         alt="{{ $articulo->titulo }}"
                         width="400" height="225"
                         loading="lazy"
                         class="blog-card-img">
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
                    <p class="blog-card-excerpt">{{ Str::limit($articulo->extracto, 140) }}</p>
                    @endif
                    <div class="blog-card-meta">
                        @if($articulo->autor)
                        <span>{{ $articulo->autor }}</span>
                        @endif
                        @if($articulo->fecha_publicacion)
                        <time datetime="{{ $articulo->fecha_publicacion->toDateString() }}">
                            {{ $articulo->fecha_publicacion->format('d M Y') }}
                        </time>
                        @endif
                    </div>
                </div>
            </article>
            @endforeach
        </div>

        <div class="pagination">
            {{ $articulos->links() }}
        </div>
        @else
        <p style="text-align:center;color:#6b7280;padding:4rem 0">Próximamente publicaremos artículos sobre comercio local y fidelización.</p>
        @endif
    </div>
</section>

@endsection

@push('head')
<style>
.blog-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
.blog-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; transition: box-shadow .2s; }
.blog-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.1); }
.blog-card-img { width: 100%; height: 200px; object-fit: cover; }
.blog-card-body { padding: 1.25rem; }
.blog-category { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6c3fc5; }
.blog-card-title { font-size: 1.05rem; margin: 0.5rem 0; line-height: 1.4; }
.blog-card-title a:hover { color: #6c3fc5; }
.blog-card-excerpt { font-size: 0.875rem; color: #6b7280; margin-bottom: 1rem; }
.blog-card-meta { display: flex; gap: 1rem; font-size: 0.8rem; color: #9ca3af; }
@media(max-width:768px){ .blog-grid { grid-template-columns: 1fr; } }
</style>
@endpush
