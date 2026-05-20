{{-- resources/views/blog/serie.blade.php --}}
@extends('layouts.app')

@section('content')

{{-- Hero --}}
<div style="background:linear-gradient(135deg,#6d007e,#b12140);padding:3rem 0 2rem">
  <div class="container">
    @if($serie->categoriaBlog)
    <a href="/blog/categoria/{{ $serie->categoriaBlog->slug }}"
       style="display:inline-block;background:rgba(255,255,255,.18);color:#fff;font-size:.75rem;font-weight:700;padding:.25rem .75rem;border-radius:99px;text-decoration:none;margin-bottom:1rem;text-transform:uppercase;letter-spacing:.05em">
      {{ $serie->categoriaBlog->nombre }}
    </a>
    @endif
    <p style="color:rgba(255,255,255,.7);font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.5rem">Serie de artículos</p>
    <h1 style="color:#fff;font-size:2rem;font-weight:900;margin:0 0 1rem;line-height:1.2">{{ $serie->nombre }}</h1>
    @if($serie->descripcion)
    <p style="color:rgba(255,255,255,.85);font-size:1.05rem;max-width:600px;line-height:1.6;margin:0">{{ $serie->descripcion }}</p>
    @endif
    <p style="color:rgba(255,255,255,.6);font-size:.85rem;margin-top:1rem">
      {{ $serie->articulos->where('estado','publicado')->count() }} artículos publicados
      @if($serie->articulos->where('estado','programado')->count() > 0)
       · {{ $serie->articulos->where('estado','programado')->count() }} próximamente
      @endif
    </p>
  </div>
</div>

{{-- Article list --}}
<div class="container" style="padding:2.5rem 1rem;max-width:760px">
  <ol style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:1.25rem">
    @foreach($serie->articulos as $art)
    @php $publicado = $art->estado === 'publicado'; @endphp
    <li style="display:flex;gap:1.25rem;align-items:flex-start;background:#fff;border:1px solid #f3f4f6;border-radius:12px;padding:1.25rem;{{ !$publicado ? 'opacity:.65' : '' }}">
      <div style="width:36px;height:36px;background:{{ $publicado ? 'linear-gradient(135deg,#6d007e,#b12140)' : '#e5e7eb' }};border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.85rem;font-weight:800;color:{{ $publicado ? '#fff' : '#9ca3af' }}">
        {{ $art->orden_en_serie }}
      </div>
      <div style="flex:1;min-width:0">
        @if($publicado)
        <a href="/blog/{{ $art->slug }}" style="font-size:1rem;font-weight:700;color:#1f2937;text-decoration:none;line-height:1.3;display:block;margin-bottom:.35rem">
          {{ $art->titulo }}
        </a>
        @else
        <p style="font-size:1rem;font-weight:700;color:#6b7280;margin:0 0 .35rem;line-height:1.3">
          {{ $art->titulo }}
        </p>
        @endif
        @if($art->extracto)
        <p style="font-size:.875rem;color:#6b7280;margin:0 0 .5rem;line-height:1.5">{{ Str::limit($art->extracto, 120) }}</p>
        @endif
        <div style="display:flex;align-items:center;gap:.75rem;font-size:.78rem">
          @if($publicado && $art->fecha_publicacion)
          <span style="color:#9ca3af">{{ $art->fecha_publicacion->locale('es')->isoFormat('D MMM YYYY') }}</span>
          @endif
          @if(!$publicado)
          <span style="background:#dbeafe;color:#1e40af;padding:.15rem .6rem;border-radius:99px;font-weight:600">Próximamente</span>
          @endif
          @if($publicado)
          <a href="/blog/{{ $art->slug }}" style="color:#7c3aed;font-weight:600;text-decoration:none">Leer →</a>
          @endif
        </div>
      </div>
    </li>
    @endforeach
  </ol>

  <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid #f3f4f6;text-align:center">
    <a href="/blog" style="color:#7c3aed;font-size:.875rem;text-decoration:none;font-weight:600">← Volver al blog</a>
  </div>
</div>

@endsection
