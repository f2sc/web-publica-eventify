@extends('layouts.app')

@section('content')

<section class="hero">
    <div class="hero-container">
        <span class="hero-badge">Para asociaciones y ayuntamientos</span>
        <h1>Digitaliza el comercio de tu barrio.</h1>
        <p>Eventify ofrece a asociaciones de comerciantes y ayuntamientos una herramienta colectiva de fidelización. Una plataforma, todos los comercios.</p>
        <div class="hero-ctas">
            <a href="{{ $appUrl }}/qr?source=para-asociaciones" class="btn btn-accent">Solicitar demo</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="benefits-grid">
            <div>
                <h2 class="section-title">Un ecosistema digital para todo el comercio local</h2>
                <p style="color:#6b7280;margin-bottom:2rem">Con Eventify, tu asociación puede ofrecer a todos sus socios una herramienta de fidelización profesional sin que cada uno tenga que buscarla por su cuenta.</p>
                <ul class="benefits-list">
                    <li>
                        <span class="benefit-icon">🏪</span>
                        <div>
                            <strong>Un QR por comercio</strong>
                            <p>Cada socio tiene su propio código QR, su base de clientes y su panel de control.</p>
                        </div>
                    </li>
                    <li>
                        <span class="benefit-icon">📣</span>
                        <div>
                            <strong>Campañas colectivas</strong>
                            <p>La asociación puede lanzar campañas que lleguen a los clientes de todos los comercios a la vez.</p>
                        </div>
                    </li>
                    <li>
                        <span class="benefit-icon">📈</span>
                        <div>
                            <strong>Métricas agregadas</strong>
                            <p>Visualiza el impacto global de la dinamización: clientes totales, visitas, comercios activos.</p>
                        </div>
                    </li>
                    <li>
                        <span class="benefit-icon">🏛️</span>
                        <div>
                            <strong>Justificación para subvenciones</strong>
                            <p>Genera informes de uso para justificar ayudas de digitalización del comercio local.</p>
                        </div>
                    </li>
                </ul>
            </div>
            <div>
                <img src="{{ asset('images/big/reunion-asociacion-comerciantes-tablet-eventify.png') }}"
                     alt="Reunión de asociación de comerciantes con tablet mostrando Eventify"
                     width="540" height="400" loading="lazy"
                     style="border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.12);width:100%">
            </div>
        </div>
    </div>
</section>

<section class="section" style="background:#f9fafb;text-align:center">
    <div class="container">
        <h2 class="section-title">¿Quieres una demo para tu asociación?</h2>
        <p class="section-subtitle" style="margin:0 auto 2.5rem">Te mostramos cómo funciona adaptado a tu caso concreto.</p>
        <a href="{{ $appUrl }}/qr?source=para-asociaciones-cta" class="btn btn-primary" style="font-size:1.1rem;padding:1rem 2.5rem">Solicitar demo gratuita →</a>
    </div>
</section>

@endsection

@push('head')
<style>
.benefits-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; }
.benefits-list { display: flex; flex-direction: column; gap: 1.5rem; }
.benefits-list li { display: flex; gap: 1rem; align-items: flex-start; }
.benefit-icon { font-size: 1.5rem; flex-shrink: 0; }
.benefits-list strong { display: block; margin-bottom: 0.25rem; }
.benefits-list p { color: #6b7280; font-size: 0.9rem; margin: 0; }
@media(max-width:768px){ .benefits-grid { grid-template-columns: 1fr; } }
</style>
@endpush
