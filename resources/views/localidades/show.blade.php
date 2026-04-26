@extends('layouts.app')

@section('content')

<section class="loc-hero">
    <div class="container">
        <x-breadcrumb :items="$breadcrumb" />
        <h1>{{ $nombre }}</h1>
        <p>Comercios con programa de fidelización Eventify en {{ $nombre }}.</p>
        @if(isset($categoriaActiva))
        <p class="categoria-label">Categoría: <strong>{{ $categoriaActiva }}</strong></p>
        @endif
    </div>
</section>

<section class="section">
    <div class="container">
        @if(count($comercios) > 0)
        <div class="comercios-grid">
            @foreach($comercios as $comercio)
                <x-comercio-card :comercio="$comercio" />
            @endforeach
        </div>
        @else
        <div style="text-align:center;padding:3rem 0;color:#6b7280">
            <p>No hay comercios disponibles en esta localidad todavía.</p>
            <a href="{{ url('/localidades') }}" class="btn btn-secondary" style="margin-top:1rem">Ver otras localidades</a>
        </div>
        @endif
    </div>
</section>

{{-- Texto SEO contextual --}}
<section class="section" style="background:#f9fafb">
    <div class="container">
        <div class="seo-text">
            <h2>Apoya el comercio local en {{ $localidad['nombre'] ?? $nombre }}</h2>
            <p>Los comercios adheridos a Eventify en {{ $nombre }} ofrecen un programa de fidelización digital que premia a sus clientes habituales. Escanea el QR del comercio con tu móvil, regístrate en segundos y empieza a recibir sus mejores ofertas directamente en tu teléfono.</p>
            <p>Sin descargas ni contraseñas. Solo tu nombre, tu número y el QR del comercio que quieres apoyar.</p>
        </div>

        <div style="margin-top:2.5rem;padding:2rem;background:#fff;border-radius:12px;border:1px solid #e5e7eb">
            <h3 style="margin-bottom:0.75rem">¿Tienes un comercio en {{ $nombre }}?</h3>
            <p style="color:#6b7280;margin-bottom:1.5rem">Únete a Eventify gratis y empieza a fidelizar a tus clientes.</p>
            <a href="https://app.eventify.es/qr?source=localidad&zona={{ $localidad['slug'] ?? '' }}" class="btn btn-primary">Registrar mi comercio</a>
        </div>
    </div>
</section>

@endsection

@push('head')
<style>
.loc-hero { background: linear-gradient(135deg, #1a1a2e 0%, #6c3fc5 100%); color: #fff; padding: 3rem 0 2.5rem; }
.loc-hero .breadcrumb-list { color: rgba(255,255,255,.7); }
.loc-hero .breadcrumb-list a { color: rgba(255,255,255,.9); }
.loc-hero h1 { font-size: clamp(1.75rem, 3vw, 2.5rem); font-weight: 800; margin: 1rem 0 0.5rem; }
.loc-hero p { color: rgba(255,255,255,.85); font-size: 1.05rem; }
.categoria-label { margin-top: 0.5rem; color: rgba(255,255,255,.7); font-size: 0.9rem; }
.seo-text p { color: #4b5563; margin-bottom: 1rem; }
.seo-text h2 { font-size: 1.4rem; margin-bottom: 1rem; }
</style>
@endpush
