@extends('layouts.app')

@section('content')

@php
    use League\CommonMark\CommonMarkConverter;
    $contenido = $articulo->contenido ?? '';
    if (preg_match('/^\s*</', $contenido)) {
        $contenidoHtml = $contenido;
    } else {
        $converter = new CommonMarkConverter([
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
        ]);
        $contenidoHtml = $converter->convert($contenido);
    }

    // Artículos relacionados: misma categoría, excluyendo el actual
    $relacionados = collect();
    if ($articulo->categoriaBlog) {
        $relacionados = \App\Models\Articulo::publicados()
            ->where('categoria_blog_id', $articulo->categoriaBlog->id)
            ->where('id', '!=', $articulo->id)
            ->orderByDesc('fecha_publicacion')
            ->limit(3)
            ->get();
    }
    if ($relacionados->count() < 2) {
        $relacionados = \App\Models\Articulo::publicados()
            ->where('id', '!=', $articulo->id)
            ->orderByDesc('fecha_publicacion')
            ->limit(3)
            ->get();
    }

    $appUrl = config('services.eventify.app_url', env('EVENTIFY_APP_URL', '#'));
@endphp

@if($preview ?? false)
@php
    $estaVivo = $articulo->estado === 'publicado'
        && (!$articulo->fecha_publicacion || $articulo->fecha_publicacion->lte(now()));
    if ($articulo->estado === 'publicado' && $articulo->fecha_publicacion?->gt(now())) {
        $previewMsg = 'Programado para publicarse el ' . $articulo->fecha_publicacion->format('d/m/Y \a \l\a\s H:i') . '. Aún no es accesible públicamente.';
    } elseif ($articulo->estado === 'borrador') {
        $previewMsg = 'Este artículo está en borrador y solo es visible para administradores.';
    } else {
        $previewMsg = 'Estado: ' . $articulo->estado . '. Solo visible para administradores.';
    }
@endphp
<div style="position:sticky;top:0;z-index:999;background:#fef3c7;border-bottom:2px solid #f59e0b;padding:.6rem 1.25rem;display:flex;align-items:center;gap:.75rem;font-size:.85rem;color:#78350f">
    <span style="font-size:1.1rem">⚠</span>
    <div><strong>Vista previa</strong> &nbsp;·&nbsp; {{ $previewMsg }}</div>
    <a href="{{ route('admin.articulos.edit', $articulo) }}"
       style="margin-left:auto;white-space:nowrap;background:#f59e0b;color:#fff;padding:.3rem .85rem;border-radius:6px;font-weight:600;text-decoration:none;font-size:.8rem">
        ← Volver al editor
    </a>
</div>
@endif

{{-- ═══ HERO ═══════════════════════════════════════════════════════════════ --}}
<div class="art-hero">
    @if($articulo->imagen_principal)
    <div class="art-hero-bg" style="background-image:url('{{ $articulo->imagen_principal }}')"></div>
    <div class="art-hero-ov"></div>
    @else
    <div class="art-hero-bg art-hero-bg--plain"></div>
    @endif

    <div class="art-hero-inner container">
        <div class="art-hero-meta-top">
            @if($articulo->categoria_blog)
            <span class="art-cat-pill">{{ $articulo->categoria_blog }}</span>
            @endif
            <span class="art-readtime">&#x23F1; {{ $articulo->tiempoLectura() }} min de lectura</span>
        </div>

        <h1 class="art-title">{{ $articulo->titulo }}</h1>

        @if($articulo->extracto)
        <p class="art-lead">{{ $articulo->extracto }}</p>
        @endif

        <div class="art-byline">
            <div class="art-avatar">{{ strtoupper(substr($articulo->autor ?? 'E', 0, 1)) }}</div>
            <div>
                <div class="art-byline-name">{{ $articulo->autor ?? 'Equipo Eventify' }}</div>
                @if($articulo->fecha_publicacion)
                <time class="art-byline-date" datetime="{{ $articulo->fecha_publicacion->toDateString() }}">
                    {{ $articulo->fecha_publicacion->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                </time>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ═══ CUERPO DEL ARTÍCULO ════════════════════════════════════════════════ --}}
{{-- Serie banner --}}
@if($articulo->serie_id && $articulo->serie)
@php $totalEnSerie = $articulo->serie->articulos()->publicados()->count(); @endphp
<div style="background:#f5f3ff;border-bottom:1px solid #ede9fe;padding:.65rem 0">
  <div class="container" style="font-size:.82rem;color:#7c3aed;display:flex;align-items:center;gap:.4rem;flex-wrap:wrap">
    <span style="font-weight:700">Serie:</span>
    <a href="{{ route('blog.serie', $articulo->serie->slug) }}" style="color:#7c3aed;font-weight:700;text-decoration:none">
      {{ $articulo->serie->nombre }}
    </a>
    <span style="color:#a78bfa">— Parte {{ $articulo->orden_en_serie }} de {{ $totalEnSerie }}</span>
  </div>
</div>
@endif
<div class="art-layout container">

    {{-- Contenido principal --}}
    <main class="art-content" id="art-content">
        <div class="art-prose">
            {!! $contenidoHtml !!}
        </div>

        {{-- Etiquetas --}}
        @if(count($articulo->etiquetasArray()) > 0)
        <div class="art-tags">
            @foreach($articulo->etiquetasArray() as $tag)
            <span class="art-tag">{{ $tag }}</span>
            @endforeach
        </div>
        @endif

        {{-- CTA compartir --}}
        <div class="art-share">
            <span class="art-share-label">¿Te ha sido útil? Compártelo:</span>
            <a href="https://twitter.com/intent/tweet?text={{ urlencode($articulo->titulo) }}&url={{ urlencode(url()->current()) }}"
               class="art-share-btn art-share-x" target="_blank" rel="noopener" aria-label="Compartir en X">
                𝕏
            </a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
               class="art-share-btn art-share-li" target="_blank" rel="noopener" aria-label="Compartir en LinkedIn">
                in
            </a>
            <a href="https://wa.me/?text={{ urlencode($articulo->titulo . ' ' . url()->current()) }}"
               class="art-share-btn art-share-wa" target="_blank" rel="noopener" aria-label="Compartir en WhatsApp">
                &#9742;
            </a>
        </div>

        {{-- FAQ --}}
        @if(!empty($articulo->faq_json) && is_array($articulo->faq_json))
        <section class="art-faq" aria-label="Preguntas frecuentes">
            <h2>Preguntas frecuentes</h2>
            @foreach($articulo->faq_json as $faq)
            @if(!empty($faq['question']))
            <details class="art-faq-item">
                <summary class="art-faq-q">{{ $faq['question'] }}</summary>
                <p class="art-faq-a">{{ $faq['answer'] }}</p>
            </details>
            @endif
            @endforeach
        </section>
        @endif

        {{-- Anterior / Siguiente en la serie --}}
        @if(isset($anterior) || isset($siguiente))
        <nav style="border-top:1px solid #f3f4f6;margin-top:2rem;padding-top:1.5rem;display:grid;grid-template-columns:1fr 1fr;gap:1rem" aria-label="Navegación de la serie">
          <div>
            @if($anterior)
            <a href="/blog/{{ $anterior->slug }}"
               style="display:block;background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:.85rem 1rem;text-decoration:none;color:#374151">
              <span style="font-size:.72rem;font-weight:600;color:#9ca3af;display:block;margin-bottom:.3rem">← Anterior</span>
              <span style="font-size:.85rem;font-weight:600;color:#1f2937;line-height:1.35;display:block">{{ Str::limit($anterior->titulo, 55) }}</span>
            </a>
            @endif
          </div>
          <div style="text-align:right">
            @if($siguiente)
            <a href="/blog/{{ $siguiente->slug }}"
               style="display:block;background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:.85rem 1rem;text-decoration:none;color:#374151">
              <span style="font-size:.72rem;font-weight:600;color:#9ca3af;display:block;margin-bottom:.3rem">Siguiente →</span>
              <span style="font-size:.85rem;font-weight:600;color:#1f2937;line-height:1.35;display:block">{{ Str::limit($siguiente->titulo, 55) }}</span>
            </a>
            @endif
          </div>
        </nav>
        @endif

    </main>

    {{-- Sidebar --}}
    <aside class="art-sidebar">

        {{-- CTA destacado --}}
        <div class="art-sidebar-cta">
            <div class="art-sidebar-cta-icon">&#x1F4F1;</div>
            <h3>¿Tienes un comercio local?</h3>
            <p>Crea tu cuenta gratis y empieza a fidelizar clientes con QR hoy mismo.</p>
            <a href="{{ $appUrl }}/register?source=blog-articulo" class="btn btn-primary" style="width:100%;text-align:center;display:block">
                Empezar gratis →
            </a>
        </div>

        {{-- Categoría --}}
        @if($articulo->categoriaBlog)
        <div class="art-sidebar-box">
            <div class="art-sidebar-box-label">Categoría</div>
            <a href="{{ url('/blog/categoria/' . $articulo->categoriaBlog->slug) }}" class="art-sidebar-cat-link">
                {{ $articulo->categoriaBlog->nombre }}
            </a>
            @if($articulo->categoriaBlog->descripcion)
            <p class="art-sidebar-cat-desc">{{ Str::limit($articulo->categoriaBlog->descripcion, 100) }}</p>
            @endif
        </div>
        @endif

    </aside>
</div>

{{-- ═══ ARTÍCULOS RELACIONADOS ════════════════════════════════════════════ --}}
@if($relacionados->count() > 0)
<section class="art-related">
    <div class="container">
        <h2 class="art-related-title">También te puede interesar</h2>
        <div class="art-related-grid">
            @foreach($relacionados as $rel)
            <article class="blog-card">
                @if($rel->imagen_principal)
                <a href="{{ url('/blog/' . $rel->slug) }}">
                    <img src="{{ $rel->imagen_principal }}" alt="{{ $rel->titulo }}" loading="lazy" class="blog-card-img">
                </a>
                @endif
                <div class="blog-card-body">
                    @if($rel->categoria_blog)
                    <span class="blog-category">{{ $rel->categoria_blog }}</span>
                    @endif
                    <h3 class="blog-card-title">
                        <a href="{{ url('/blog/' . $rel->slug) }}">{{ $rel->titulo }}</a>
                    </h3>
                    @if($rel->extracto)
                    <p class="blog-card-excerpt">{{ Str::limit($rel->extracto, 100) }}</p>
                    @endif
                    <div class="blog-card-meta">
                        <span>{{ $rel->autor ?? 'Equipo Eventify' }}</span>
                        <span class="blog-readtime">{{ $rel->tiempoLectura() }} min</span>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
        <div style="text-align:center;margin-top:2rem">
            <a href="{{ url('/blog') }}" class="btn btn-secondary">← Ver todos los artículos</a>
        </div>
    </div>
</section>
@endif

@endsection

@push('head')
<style>
/* ── HERO ─────────────────────────────────────────────────────────────── */
.art-hero {
    position: relative;
    min-height: 420px;
    display: flex;
    align-items: flex-end;
    padding-bottom: 3rem;
    overflow: hidden;
}
.art-hero-bg {
    position: absolute; inset: 0;
    background-size: cover;
    background-position: center 30%;
    animation: heroZoom 7s ease-out both;
}
@keyframes heroZoom { from { transform: scale(1.08); } to { transform: scale(1); } }
.art-hero-bg--plain { background: var(--grad-brand); }
.art-hero-ov {
    position: absolute; inset: 0;
    background: linear-gradient(160deg, rgba(30,10,60,.45) 0%, rgba(10,5,30,.82) 100%);
}
.art-hero-inner {
    position: relative; z-index: 1;
    padding-top: 6rem;
}


.art-hero-meta-top {
    display: flex; gap: 1rem; align-items: center;
    margin: 1rem 0 .75rem;
    flex-wrap: wrap;
}
.art-cat-pill {
    background: var(--brand);
    color: #fff;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    padding: .25rem .7rem;
    border-radius: 99px;
}
.art-readtime {
    color: rgba(255,255,255,.7);
    font-size: .85rem;
}
.art-title {
    font-size: clamp(1.6rem, 3.5vw, 2.6rem);
    font-weight: 900;
    line-height: 1.2;
    color: #fff;
    margin-bottom: .9rem;
    max-width: 800px;
    text-shadow: 0 2px 12px rgba(0,0,0,.3);
}
.art-lead {
    font-size: 1.1rem;
    color: rgba(255,255,255,.82);
    line-height: 1.6;
    max-width: 680px;
    margin-bottom: 1.5rem;
}
.art-byline {
    display: flex; align-items: center; gap: .85rem;
}
.art-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: var(--brand);
    color: #fff;
    font-size: .95rem;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    border: 2px solid rgba(255,255,255,.3);
}
.art-byline-name {
    color: #fff;
    font-weight: 600;
    font-size: .9rem;
}
.art-byline-date {
    color: rgba(255,255,255,.6);
    font-size: .82rem;
}

/* ── LAYOUT 2 COLUMNAS ────────────────────────────────────────────────── */
.art-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 3rem;
    padding-top: 3rem;
    padding-bottom: 3rem;
    align-items: start;
}

/* ── PROSA ────────────────────────────────────────────────────────────── */
.art-prose {
    font-size: 1.05rem;
    line-height: 1.85;
    color: #374151;
}
/* ── FAQ ──────────────────────────────────────────────────────────────── */
.art-faq {
    margin-top: 2.5rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}
.art-faq h2 {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--navy);
    margin-bottom: 1rem;
}
.art-faq-item {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: .5rem;
    overflow: hidden;
}
.art-faq-q {
    padding: .85rem 1rem;
    font-size: .95rem;
    font-weight: 600;
    cursor: pointer;
    color: #374151;
    list-style: none;
}
.art-faq-q::-webkit-details-marker { display: none; }
.art-faq-q::before { content: '+ '; color: var(--brand); font-weight: 700; }
details[open] .art-faq-q::before { content: '− '; }
.art-faq-a {
    padding: .75rem 1rem 1rem;
    font-size: .9rem;
    color: #4b5563;
    line-height: 1.65;
    margin: 0;
    background: #f9fafb;
}

.art-prose h2 {
    font-size: 1.45rem;
    font-weight: 800;
    color: var(--navy);
    margin: 2.2rem 0 .8rem;
    line-height: 1.3;
}
.art-prose h3 {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--navy);
    margin: 1.8rem 0 .6rem;
}
.art-prose p { margin-bottom: 1.3rem; }
.art-prose strong { color: var(--navy); font-weight: 700; }
.art-prose ul, .art-prose ol {
    padding-left: 1.5rem;
    margin-bottom: 1.3rem;
}
.art-prose li { margin-bottom: .4rem; }
.art-prose blockquote {
    border-left: 4px solid var(--brand);
    background: #f5f3ff;
    margin: 1.8rem 0;
    padding: 1.1rem 1.5rem;
    border-radius: 0 8px 8px 0;
    font-style: italic;
    color: #4b5563;
    font-size: 1.05rem;
    line-height: 1.7;
}
.art-prose blockquote p { margin: 0; }
.art-prose a { color: var(--brand); text-decoration: underline; }
.art-prose a:hover { color: var(--navy); }
.art-prose code {
    background: #f3f4f6; color: #6c3fc5;
    padding: .15em .4em; border-radius: 4px; font-size: .9em;
}
.art-prose hr {
    border: none; border-top: 2px solid #f3f4f6;
    margin: 2.5rem 0;
}

/* ── ETIQUETAS Y COMPARTIR ────────────────────────────────────────────── */
.art-tags {
    display: flex; flex-wrap: wrap; gap: .5rem;
    margin: 2rem 0 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #f3f4f6;
}
.art-tag {
    background: #e8deff; color: #6c3fc5;
    font-size: .78rem; padding: .25rem .75rem;
    border-radius: 99px;
}
.art-share {
    display: flex; align-items: center; gap: .6rem;
    margin-top: 2rem;
    padding: 1.25rem 1.5rem;
    background: #f9fafb;
    border-radius: 10px;
}
.art-share-label { font-size: .85rem; color: #6b7280; flex: 1; }
.art-share-btn {
    width: 34px; height: 34px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .85rem;
    text-decoration: none;
    transition: opacity .2s;
}
.art-share-btn:hover { opacity: .8; }
.art-share-x  { background: #000; color: #fff; }
.art-share-li { background: #0a66c2; color: #fff; }
.art-share-wa { background: #25d366; color: #fff; }

/* ── SIDEBAR ──────────────────────────────────────────────────────────── */
.art-sidebar { position: sticky; top: 5rem; }
.art-sidebar-cta {
    background: var(--grad-brand);
    color: #fff;
    border-radius: 14px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    text-align: center;
}
.art-sidebar-cta-icon { font-size: 2rem; margin-bottom: .5rem; }
.art-sidebar-cta h3 { font-size: 1rem; font-weight: 800; margin-bottom: .5rem; color: #fff; }
.art-sidebar-cta p  { font-size: .85rem; color: rgba(255,255,255,.8); margin-bottom: 1rem; }
.art-sidebar-cta .btn-primary { background: #fff; color: var(--brand); }
.art-sidebar-cta .btn-primary:hover { background: var(--warm); }

.art-sidebar-box {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.25rem;
}
.art-sidebar-box-label {
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: #9ca3af; margin-bottom: .5rem;
}
.art-sidebar-cat-link {
    display: inline-block;
    color: var(--brand); font-weight: 700; font-size: .95rem;
    text-decoration: none;
    margin-bottom: .5rem;
}
.art-sidebar-cat-link:hover { text-decoration: underline; }
.art-sidebar-cat-desc { font-size: .82rem; color: #6b7280; line-height: 1.5; margin: 0; }

/* ── RELACIONADOS ─────────────────────────────────────────────────────── */
.art-related {
    background: #f9fafb;
    padding: 3.5rem 0;
    border-top: 1px solid #e5e7eb;
}
.art-related-title {
    font-size: 1.35rem; font-weight: 800; color: var(--navy);
    margin-bottom: 2rem; text-align: center;
}
.art-related-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}
/* Reutilizamos estilos blog-card del index */
.art-related-grid .blog-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: box-shadow .2s; }
.art-related-grid .blog-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.1); }
.art-related-grid .blog-card-img { width: 100%; height: 180px; object-fit: cover; display: block; }
.art-related-grid .blog-card-body { padding: 1.1rem; }
.art-related-grid .blog-category { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--brand); }
.art-related-grid .blog-card-title { font-size: .95rem; margin: .4rem 0 .6rem; line-height: 1.4; }
.art-related-grid .blog-card-title a { color: var(--navy); text-decoration: none; }
.art-related-grid .blog-card-title a:hover { color: var(--brand); }
.art-related-grid .blog-card-excerpt { font-size: .82rem; color: var(--muted); margin-bottom: .75rem; }
.art-related-grid .blog-card-meta { display: flex; justify-content: space-between; font-size: .78rem; color: #9ca3af; }
.art-related-grid .blog-readtime { background: #f3f4f6; color: #6b7280; padding: 2px 7px; border-radius: 20px; font-size: .72rem; }

/* ── RESPONSIVE ───────────────────────────────────────────────────────── */
@media(max-width: 900px) {
    .art-layout { grid-template-columns: 1fr; }
    .art-sidebar { position: static; }
    .art-related-grid { grid-template-columns: 1fr; }
}
@media(max-width: 640px) {
    .art-hero { min-height: 320px; }
    .art-share { flex-wrap: wrap; }
}
</style>
@endpush
