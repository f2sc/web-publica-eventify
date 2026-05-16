@extends('layouts.app')

@section('content')

<div class="sub-hero">
    <div class="sub-hero-bg" style="background:var(--grad-brand);"></div>
    <div class="sub-hero-ov"></div>
    <div class="sub-hero-cnt">
        <div class="sub-ey">Directorio</div>
        <h1>Comercios locales<br>con fidelización QR</h1>
        <p>Encuentra tu localidad y descubre los comercios con programa de puntos, ofertas y notificaciones push.</p>
    </div>
</div>

<section class="section" style="background:#fff;">
    <div class="container">

        @if(count($localidades) > 0)

        <div style="text-align:center;margin-bottom:3rem;">
            <div class="eyebrow">Localidades activas</div>
            <h2 class="section-title">Elige tu localidad</h2>
            <p class="section-subtitle">{{ count($localidades) }} {{ count($localidades) === 1 ? 'localidad activa' : 'localidades activas' }} con comercios adheridos a Eventify.</p>
        </div>

        <div class="loc-cards-grid">
            @foreach($localidades as $loc)
            @php
                $nombre = $loc['nombre'] ?? $loc['name'] ?? '';
                $slug   = $loc['slug'] ?? '';
                $num    = $loc['num_comercios'] ?? null;
            @endphp
            <a href="{{ url('/localidades/' . $slug) }}" class="loc-dir-card">
                <div class="loc-dir-icon">📍</div>
                <div class="loc-dir-body">
                    <div class="loc-dir-name">{{ $nombre }}</div>
                    @if($num)
                    <div class="loc-dir-count">{{ $num }} {{ $num == 1 ? 'comercio' : 'comercios' }}</div>
                    @else
                    <div class="loc-dir-count">Ver comercios</div>
                    @endif
                </div>
                <div class="loc-dir-arrow">›</div>
            </a>
            @endforeach
        </div>

        @else
        <div style="text-align:center;padding:5rem 0;color:var(--muted);">
            <div style="font-size:3rem;margin-bottom:1rem;">📍</div>
            <p style="font-size:1.1rem;">Próximamente más localidades disponibles.</p>
        </div>
        @endif

    </div>
</section>

{{-- CTA --}}
<div class="cta-final-split">
    <div class="cta-left">
        <div class="cta-left-inner">
            <div class="eyebrow" style="color:#c4b5fd;">¿Tienes un comercio?</div>
            <h2>Lleva tu localidad<br>a Eventify.</h2>
            <p>Regístrate gratis y empieza a fidelizar a tus clientes con un QR. Sin instalaciones. Sin contratos.</p>
            <div class="cta-btns">
                <a href="{{ $appUrl }}/register?source=localidades-index" class="btn btn-accent">Registrar mi comercio gratis →</a>
            </div>
        </div>
    </div>
    <div class="cta-right">
        <img src="{{ asset('images/big/comerciante-movil-tienda-local-eventify.png') }}"
             alt="Comerciante gestionando Eventify" loading="lazy" style="object-position:center 20%;">
        <div class="cta-right-ov"></div>
    </div>
</div>

@endsection

@push('head')
<style>
.loc-cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem; max-width: 900px; margin: 0 auto; }
.loc-dir-card { display: flex; align-items: center; gap: 1rem; background: #fff; border: 1.5px solid var(--border); border-radius: 14px; padding: 1.25rem 1.5rem; transition: all .2s; text-decoration: none; }
.loc-dir-card:hover { border-color: var(--brand); box-shadow: 0 4px 16px rgba(109,0,126,.12); transform: translateY(-2px); }
.loc-dir-icon { font-size: 1.5rem; flex-shrink: 0; }
.loc-dir-body { flex: 1; min-width: 0; }
.loc-dir-name { font-weight: 800; font-size: 1rem; color: var(--navy); }
.loc-dir-count { font-size: 0.8rem; color: var(--muted); margin-top: 2px; }
.loc-dir-arrow { color: var(--brand); font-size: 1.3rem; font-weight: 700; }
</style>
@endpush
