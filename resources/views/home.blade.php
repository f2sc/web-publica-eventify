@extends('layouts.app')

@section('content')

{{-- HERO --}}
<section class="hero">
    <div class="hero-container">
        <span class="hero-badge">Fidelización QR para el comercio local</span>
        <h1>Tus clientes vuelven.<br>Tú lo ves en tiempo real.</h1>
        <p>Eventify conecta tu comercio con tus clientes mediante un código QR. Ellos escanean, tú fidelizas. Sin app, sin complicaciones.</p>
        <div class="hero-ctas">
            <a href="https://app.eventify.es/qr?source=web-home" class="btn btn-accent">Crear mi QR gratis</a>
            <a href="{{ url('/como-funciona') }}" class="btn btn-secondary" style="color:#fff;border-color:rgba(255,255,255,.5)">Cómo funciona</a>
        </div>
    </div>
</section>

{{-- CÓMO FUNCIONA — 3 pasos --}}
<section class="section" style="background:#f9fafb">
    <div class="container">
        <h2 class="section-title" style="text-align:center">En 3 pasos, fidelización real</h2>
        <p class="section-subtitle" style="text-align:center;margin:0 auto 3rem">Sin descargas, sin formación, sin complicaciones.</p>

        <div class="steps-grid">
            <article class="step-card">
                <div class="step-num">1</div>
                <h3>Imprime tu QR</h3>
                <p>Regístrate, configura tu comercio y descarga tu código QR personalizado. Lo pones en el mostrador y listo.</p>
            </article>
            <article class="step-card">
                <div class="step-num">2</div>
                <h3>El cliente escanea</h3>
                <p>El cliente apunta su móvil al QR, introduce su nombre y móvil — sin app, sin contraseña.</p>
            </article>
            <article class="step-card">
                <div class="step-num">3</div>
                <h3>Tú envías, él vuelve</h3>
                <p>Manda notificaciones push con ofertas y novedades. Los clientes las reciben directamente en su móvil.</p>
            </article>
        </div>
    </div>
</section>

{{-- BENEFICIOS --}}
<section class="section">
    <div class="container">
        <div class="benefits-grid">
            <div class="benefits-text">
                <h2 class="section-title">Todo lo que necesita tu negocio</h2>
                <ul class="benefits-list">
                    <li>
                        <span class="benefit-icon">📲</span>
                        <div>
                            <strong>Captación sin fricciones</strong>
                            <p>El cliente se registra en 10 segundos con solo su nombre y teléfono.</p>
                        </div>
                    </li>
                    <li>
                        <span class="benefit-icon">🔔</span>
                        <div>
                            <strong>Notificaciones push directas</strong>
                            <p>Llega al móvil de tus clientes cuando tú quieras, sin depender del algoritmo.</p>
                        </div>
                    </li>
                    <li>
                        <span class="benefit-icon">📊</span>
                        <div>
                            <strong>Panel de control completo</strong>
                            <p>Visualiza cuántos clientes tienes, cuándo vuelven y qué ofertas funcionan mejor.</p>
                        </div>
                    </li>
                    <li>
                        <span class="benefit-icon">🤝</span>
                        <div>
                            <strong>Red de comercios local</strong>
                            <p>Colabora con otros comercios de tu asociación y comparte clientes con afinidad.</p>
                        </div>
                    </li>
                </ul>
                <a href="https://app.eventify.es/qr?source=web-benefits" class="btn btn-primary" style="margin-top:1.5rem">Empieza gratis</a>
            </div>
            <div class="benefits-image">
                <img src="{{ asset('images/panel-control-dashboard-eventify-comercio.png') }}"
                     alt="Panel de control de Eventify mostrando estadísticas de un comercio"
                     width="540" height="400" loading="lazy"
                     style="border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.15)">
            </div>
        </div>
    </div>
</section>

{{-- LOCALIDADES --}}
@if(count($localidades) > 0)
<section class="section" style="background:#f9fafb">
    <div class="container">
        <h2 class="section-title" style="text-align:center">Comercios adheridos en tu zona</h2>
        <p class="section-subtitle" style="text-align:center;margin:0 auto 2rem">Localidades donde ya usamos Eventify.</p>

        <ul class="localidades-grid" role="list">
            @foreach($localidades as $loc)
            <li>
                <a href="{{ url('/localidades/' . ($loc['slug'] ?? '')) }}" class="localidad-chip">
                    {{ $loc['nombre'] ?? $loc['name'] ?? '' }}
                    @if(isset($loc['num_comercios']))
                        <span class="chip-count">{{ $loc['num_comercios'] }}</span>
                    @endif
                </a>
            </li>
            @endforeach
        </ul>

        <div style="text-align:center;margin-top:2rem">
            <a href="{{ url('/localidades') }}" class="btn btn-secondary">Ver todas las localidades</a>
        </div>
    </div>
</section>
@endif

{{-- CTA FINAL --}}
<section class="section cta-final">
    <div class="container" style="text-align:center">
        <h2 class="section-title">¿Listo para fidelizar a tus clientes?</h2>
        <p class="section-subtitle" style="margin:0 auto 2rem">Regístrate gratis en 2 minutos. Sin tarjeta de crédito.</p>
        <a href="https://app.eventify.es/qr?source=web-cta-final" class="btn btn-primary btn-lg">Crear mi cuenta gratis</a>
    </div>
</section>

@endsection

@push('head')
<style>
.steps-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
.step-card { background: #fff; border-radius: 12px; padding: 2rem; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
.step-num { width: 48px; height: 48px; background: #6c3fc5; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; margin: 0 auto 1rem; }
.step-card h3 { font-size: 1.1rem; margin-bottom: 0.5rem; }
.step-card p { color: #6b7280; font-size: 0.95rem; }
.benefits-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; }
.benefits-list { display: flex; flex-direction: column; gap: 1.5rem; margin-top: 1.5rem; }
.benefits-list li { display: flex; gap: 1rem; align-items: flex-start; }
.benefit-icon { font-size: 1.5rem; flex-shrink: 0; }
.benefits-list strong { display: block; font-size: 1rem; margin-bottom: 0.25rem; }
.benefits-list p { color: #6b7280; font-size: 0.9rem; margin: 0; }
.localidades-grid { display: flex; flex-wrap: wrap; gap: 0.75rem; justify-content: center; }
.localidad-chip { display: inline-flex; align-items: center; gap: 0.5rem; background: #fff; border: 1px solid #e5e7eb; border-radius: 99px; padding: 0.4rem 1rem; font-size: 0.9rem; color: #374151; transition: all .2s; }
.localidad-chip:hover { border-color: #6c3fc5; color: #6c3fc5; background: #f5f0ff; }
.chip-count { background: #6c3fc5; color: #fff; border-radius: 99px; font-size: 0.7rem; font-weight: 700; padding: 0.1rem 0.5rem; }
.cta-final { background: linear-gradient(135deg, #1a1a2e 0%, #6c3fc5 100%); color: #fff; }
.cta-final .section-title { color: #fff; }
.cta-final .section-subtitle { color: rgba(255,255,255,.8); }
.btn-lg { padding: 1rem 2.5rem; font-size: 1.1rem; }
@media(max-width:768px){
    .steps-grid { grid-template-columns: 1fr; }
    .benefits-grid { grid-template-columns: 1fr; }
    .benefits-image { display: none; }
}
</style>
@endpush
