@props(['comercio'])

@php
    $nombre    = $comercio['nombre'] ?? '';
    $tipo      = $comercio['tipo_comercio'] ?? '';
    $slug      = $comercio['slug'] ?? '';
    $logo      = $comercio['url_logo'] ?? null;
    $direccion = trim(($comercio['direccion'] ?? '') . ' · ' . ($comercio['localidad'] ?? ''), ' · ');
    $oferta    = $comercio['oferta_activa'] ?? null;
    $iniciales = strtoupper(mb_substr($nombre, 0, 2));
@endphp

<article class="comercio-card" aria-label="{{ $nombre }}">
    @if($logo)
        <div class="card-logo-wrap">
            <img src="{{ $logo }}" alt="Logo {{ $nombre }}" loading="lazy" style="width:100%;height:140px;object-fit:cover;">
        </div>
    @else
        <div class="card-initials" aria-hidden="true">{{ $iniciales }}</div>
    @endif

    <div class="card-body">
        <h3 class="card-name">{{ $nombre }}</h3>
        @if($tipo)
            <p class="card-type">{{ $tipo }}</p>
        @endif
        @if($oferta)
            <p class="card-offer">
                <span class="offer-badge">Oferta</span>
                {{ $oferta }}
            </p>
        @endif
        @if($direccion)
            <p class="card-address">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                </svg>
                {{ $direccion }}
            </p>
        @endif

        <a href="{{ $appUrl }}/comercio/{{ $slug }}"
           class="btn-card"
           aria-label="Ver página de {{ $nombre }}">
            Ver comercio
        </a>
    </div>
</article>
