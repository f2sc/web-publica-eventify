@props(['comercio'])

@php
    $nombre   = $comercio['nombre'] ?? '';
    $tipo     = $comercio['tipo_comercio'] ?? '';
    $slug     = $comercio['slug'] ?? '';
    $logo     = $comercio['url_logo'] ?? null;
    $direccion = trim(($comercio['direccion'] ?? '') . ', ' . ($comercio['localidad'] ?? ''), ', ');
    $oferta   = $comercio['oferta_activa'] ?? null;
    $iniciales = strtoupper(mb_substr($nombre, 0, 2));
@endphp

<article class="comercio-card" aria-label="{{ $nombre }}">
    <div class="card-logo">
        @if($logo)
            <img src="{{ $logo }}" alt="Logo {{ $nombre }}" width="64" height="64" loading="lazy">
        @else
            <div class="card-initials" aria-hidden="true">{{ $iniciales }}</div>
        @endif
    </div>

    <div class="card-body">
        <h3 class="card-name">{{ $nombre }}</h3>
        @if($tipo)
            <p class="card-type">{{ $tipo }}</p>
        @endif
        @if($direccion)
            <p class="card-address">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                </svg>
                {{ $direccion }}
            </p>
        @endif
        @if($oferta)
            <p class="card-offer">
                <span class="offer-badge">Oferta</span>
                {{ $oferta }}
            </p>
        @endif
    </div>

    <div class="card-action">
        <a href="https://app.eventify.es/comercio/{{ $slug }}"
           class="btn-card"
           aria-label="Ver página de {{ $nombre }}">
            Ver comercio
        </a>
    </div>
</article>
