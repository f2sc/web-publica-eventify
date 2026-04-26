@extends('layouts.app')

@section('content')

<div class="loc-hero">
    <div class="loc-hero-bg"></div>
    <div class="loc-hero-ov"></div>
    <div class="loc-hero-cnt">
        <x-breadcrumb :items="$breadcrumb" />
        <h1>{{ $nombre }}</h1>
        <p>Comercios con programa de fidelización Eventify en {{ $nombre }}.</p>
        @if(isset($categoriaActiva))
        <p class="categoria-label">Categoría: <strong>{{ $categoriaActiva }}</strong></p>
        @endif
    </div>
</div>

<section class="section">
    <div class="container">
        @if(count($comercios) > 0)
        <div class="comercios-grid">
            @foreach($comercios as $comercio)
                <x-comercio-card :comercio="$comercio" />
            @endforeach
        </div>
        @else
        <div style="text-align:center;padding:4rem 0;color:var(--muted);">
            <p style="font-size:1.1rem;margin-bottom:1.5rem;">No hay comercios disponibles en esta localidad todavía.</p>
            <a href="{{ url('/localidades') }}" class="btn btn-secondary">Ver otras localidades</a>
        </div>
        @endif
    </div>
</section>

{{-- Texto SEO + CTA para comercios --}}
<section class="section" style="background:var(--warm);padding-top:0;">
    <div class="container">
        <div class="seo-text" style="max-width:720px;margin-bottom:2.5rem;">
            <h2>Apoya el comercio local en {{ $localidad['nombre'] ?? $nombre }}</h2>
            <p>Los comercios adheridos a Eventify en {{ $nombre }} ofrecen un programa de fidelización digital que premia a sus clientes habituales. Escanea el QR del comercio con tu móvil, regístrate en segundos y empieza a recibir sus mejores ofertas directamente en tu teléfono.</p>
            <p>Sin descargas ni contraseñas. Solo tu nombre, tu número y el QR del comercio que quieres apoyar.</p>
        </div>

        <div style="background:#fff;border-radius:var(--radius);border:1.5px solid var(--border);padding:2rem;box-shadow:var(--shadow);max-width:600px;">
            <h3 style="color:var(--navy);margin-bottom:0.75rem;font-size:1.1rem;">&#x1F4CD; ¿Tienes un comercio en {{ $nombre }}?</h3>
            <p style="color:var(--muted);margin-bottom:1.5rem;">Únete a Eventify gratis y empieza a fidelizar a tus clientes con un simple QR.</p>
            <a href="{{ $appUrl }}/qr?source=localidad&zona={{ $localidad['slug'] ?? '' }}" class="btn btn-primary">Registrar mi comercio</a>
        </div>
    </div>
</section>

@endsection
