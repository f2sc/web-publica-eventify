@props(['comercio'])

@php
    $nombre    = $comercio['nombre_comercial'] ?? $comercio['nombre'] ?? '';
    $categoria = $comercio['categoria']['nombre'] ?? $comercio['tipo_comercio'] ?? '';
    $slug          = $comercio['slug'] ?? '';
    $codigoComer   = $comercio['codigo_comercio'] ?? $slug;
    $logo      = $comercio['url_logo'] ?? null;
    $imgBg     = $comercio['url_img_cabecera'] ?? $comercio['url_img_fachada'] ?? null;
    $ciudad    = $comercio['localidad']['nombre'] ?? (is_string($comercio['localidad'] ?? null) ? $comercio['localidad'] : '');
    $iniciales = strtoupper(mb_substr($nombre, 0, 2, 'UTF-8'));
    $gradients = ['linear-gradient(135deg,#6d007e,#b12140)','linear-gradient(135deg,#b12140,#6d007e)','linear-gradient(135deg,#9d1060,#6d007e)'];
    $grad      = $gradients[abs(crc32($nombre)) % count($gradients)];
@endphp

<article class="com-dir-card" aria-label="{{ $nombre }}">
    <div class="com-dir-img">
        @if($imgBg)
            <img src="{{ $imgBg }}" alt="{{ $nombre }}" loading="lazy">
        @elseif($logo)
            <img src="{{ $logo }}" alt="{{ $nombre }}" loading="lazy" style="object-fit:contain;padding:1rem;background:#f9f5ff;">
        @else
            <div class="com-dir-initials" style="background:{{ $grad }};">{{ $iniciales }}</div>
        @endif
        @if($logo && $imgBg)
        <div class="com-dir-logo-badge">
            <img src="{{ $logo }}" alt="Logo {{ $nombre }}" loading="lazy">
        </div>
        @endif
    </div>
    <div class="com-dir-body">
        <div class="com-dir-meta">
            @if($categoria)<span class="com-dir-cat">{{ $categoria }}</span>@endif
            @if($ciudad)<span class="com-dir-city">📍 {{ $ciudad }}</span>@endif
        </div>
        <h3 class="com-dir-name">{{ $nombre }}</h3>
        <a href="{{ $appUrl }}/c/{{ $codigoComer }}" class="com-dir-btn" aria-label="Ver {{ $nombre }} en Eventify">
            Ver en Eventify →
        </a>
    </div>
</article>
