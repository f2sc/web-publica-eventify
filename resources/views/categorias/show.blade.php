@extends('layouts.app')

@section('content')

<section class="loc-hero">
    <div class="container">
        <x-breadcrumb :items="$breadcrumb" />
        <h1>{{ $nombre }} con fidelización Eventify</h1>
        <p>Descubre los mejores {{ $nombre }} adheridos al programa de fidelización QR.</p>
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
            <p>Aún no hay {{ $nombre }} disponibles. ¡Pronto habrá más!</p>
            <a href="{{ url('/localidades') }}" class="btn btn-secondary" style="margin-top:1rem">Ver localidades</a>
        </div>
        @endif
    </div>
</section>

<section class="section" style="background:#f9fafb">
    <div class="container">
        <div class="seo-text">
            <h2>{{ $nombre }} que fidelizan a sus clientes</h2>
            <p>Los {{ $nombre }} que aparecen en este directorio utilizan Eventify para conectar con sus clientes de forma directa. Escanea el QR en la entrada del local, regístrate en segundos y empieza a recibir sus novedades y ofertas exclusivas.</p>
        </div>
        <div style="margin-top:2rem;padding:2rem;background:#fff;border-radius:12px;border:1px solid #e5e7eb">
            <h3 style="margin-bottom:0.75rem">¿Tienes un {{ rtrim($nombre, 's') }}? Únete gratis</h3>
            <a href="https://app.eventify.es/qr?source=categoria&tipo={{ $slug }}" class="btn btn-primary">Registrar mi negocio</a>
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
.seo-text p { color: #4b5563; margin-bottom: 1rem; }
.seo-text h2 { font-size: 1.4rem; margin-bottom: 1rem; }
</style>
@endpush
