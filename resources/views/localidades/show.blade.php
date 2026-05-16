@extends('layouts.app')

@section('content')

<div class="sub-hero">
    <div class="sub-hero-bg" style="background:var(--grad-brand);"></div>
    <div class="sub-hero-ov"></div>
    <div class="sub-hero-cnt">
        <x-breadcrumb :items="$breadcrumb" />
        <div class="sub-ey">Directorio local</div>
        <h1>Comercios en {{ $nombre }}</h1>
        <p>Descubre los comercios con programa de fidelización QR, ofertas y notificaciones push en {{ $nombre }}.</p>
        @if(count($comercios) > 0)
        <div class="loc-show-badge">
            <span>📍</span>
            <span>{{ count($comercios) }} {{ count($comercios) === 1 ? 'comercio adherido' : 'comercios adheridos' }}</span>
        </div>
        @endif
    </div>
</div>

{{-- Grid de comercios --}}
<section class="section" style="background:#fff;">
    <div class="container">
        @if(count($comercios) > 0)

        <div style="text-align:center;margin-bottom:2.5rem;">
            <div class="eyebrow">Mapa de fidelización</div>
            <h2 class="section-title">Estos comercios ya usan Eventify</h2>
        </div>

        <div class="com-dir-grid">
            @foreach($comercios as $comercio)
                <x-comercio-card :comercio="$comercio" />
            @endforeach
        </div>

        @else
        <div style="text-align:center;padding:5rem 0;">
            <div style="font-size:3rem;margin-bottom:1rem;">📍</div>
            <p style="font-size:1.1rem;color:var(--muted);margin-bottom:1.5rem;">Todavía no hay comercios disponibles en esta localidad.</p>
            <a href="{{ url('/localidades') }}" class="btn btn-secondary">Ver otras localidades</a>
        </div>
        @endif
    </div>
</section>

{{-- SEO + CTA split --}}
<section class="section" style="background:var(--warm);">
    <div class="container">
        <div class="loc-seo-split">
            <div class="loc-seo-text">
                <h2>Fidelización digital en {{ $nombre }}</h2>
                <p>Los comercios de {{ $nombre }} adheridos a Eventify ofrecen un programa de puntos y notificaciones push que premia a sus clientes habituales. Escanea el QR del comercio, regístrate en segundos y empieza a recibir sus mejores ofertas directamente en tu móvil.</p>
                <p>Sin descargas ni contraseñas. Solo tu nombre, tu número y el QR del comercio que quieres apoyar. Una manera sencilla de apoyar el comercio de proximidad de tu barrio.</p>
                @if(count($comercios) > 0)
                <ul class="loc-seo-list">
                    <li>✅ {{ count($comercios) }} {{ count($comercios) === 1 ? 'comercio adherido' : 'comercios adheridos' }} en {{ $nombre }}</li>
                    <li>✅ Notificaciones push de ofertas directas</li>
                    <li>✅ Sin descargar ninguna app</li>
                </ul>
                @endif
            </div>
            <div class="loc-seo-aside">
                <div class="loc-cta-card">
                    <div class="loc-cta-icon">📍</div>
                    <h3>¿Tienes un comercio en {{ $nombre }}?</h3>
                    <p>Únete a Eventify gratis y empieza a fidelizar a tus clientes con un simple QR. Sin contratos ni instalaciones.</p>
                    <a href="{{ $appUrl }}/register?source=localidad&zona={{ $localidad['slug'] ?? '' }}" class="btn btn-primary" style="width:100%;text-align:center;">Registrar mi comercio gratis →</a>
                    <a href="{{ url('/localidades') }}" style="display:block;text-align:center;margin-top:.75rem;font-size:.8rem;color:var(--muted);">← Ver otras localidades</a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('head')
<style>
.loc-show-badge { display:inline-flex; align-items:center; gap:.5rem; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); border-radius:100px; padding:.4rem 1rem; font-size:.875rem; font-weight:700; color:#fff; margin-top:1rem; }
.loc-seo-split { display:grid; grid-template-columns:1fr 380px; gap:3rem; align-items:start; }
.loc-seo-text h2 { font-size:1.5rem; font-weight:900; color:var(--navy); margin-bottom:1rem; }
.loc-seo-text p { color:#6b7280; line-height:1.7; margin-bottom:1rem; }
.loc-seo-list { list-style:none; padding:0; margin:1.25rem 0 0; display:flex; flex-direction:column; gap:.5rem; }
.loc-seo-list li { font-size:.875rem; color:var(--navy); font-weight:600; }
.loc-cta-card { background:#fff; border:1.5px solid var(--border); border-radius:16px; padding:1.75rem; box-shadow:0 4px 20px rgba(0,0,0,.06); }
.loc-cta-icon { font-size:1.75rem; margin-bottom:.75rem; }
.loc-cta-card h3 { font-size:1rem; font-weight:800; color:var(--navy); margin-bottom:.5rem; }
.loc-cta-card p { font-size:.875rem; color:#6b7280; margin-bottom:1.25rem; line-height:1.6; }
@media(max-width:900px){ .loc-seo-split { grid-template-columns:1fr; } }
</style>
@endpush
