@extends('layouts.app')

@section('content')

<section class="hero">
    <div class="hero-container">
        <span class="hero-badge">Guía rápida</span>
        <h1>Cómo funciona Eventify</h1>
        <p>Fidelización QR en 3 pasos, sin app, sin fricciones.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="steps-detailed">

            <article class="step-detailed">
                <div class="step-image">
                    <img src="{{ asset('images/big/comerciante-panel-eventify-cartel-qr-tienda.png') }}"
                         alt="Comerciante registrando su negocio en el panel Eventify y descargando el cartel QR"
                         width="500" height="360" loading="lazy">
                </div>
                <div class="step-content">
                    <span class="step-badge">Paso 1</span>
                    <h2>Regístrate y configura tu comercio</h2>
                    <p>Crea tu cuenta en <strong>app.eventify.es</strong> en 2 minutos. Añade el nombre de tu negocio, logotipo y una oferta de bienvenida para nuevos clientes. Descarga e imprime tu código QR personalizado.</p>
                    <ul class="step-list">
                        <li>Sin instalaciones ni formación técnica</li>
                        <li>QR descargable en PDF para imprimir</li>
                        <li>Panel de control en tu móvil</li>
                    </ul>
                </div>
            </article>

            <article class="step-detailed step-reversed">
                <div class="step-image">
                    <img src="{{ asset('images/big/cliente-escanea-qr-tienda-local.jpg') }}"
                         alt="Cliente escaneando el código QR de un comercio local con su teléfono móvil"
                         width="500" height="360" loading="lazy">
                </div>
                <div class="step-content">
                    <span class="step-badge">Paso 2</span>
                    <h2>El cliente escanea el QR</h2>
                    <p>El cliente apunta la cámara de su móvil al QR. En segundos ve la página de tu comercio: quién eres, qué ofreces y tu oferta de bienvenida. Introduce su nombre y móvil para registrarse.</p>
                    <ul class="step-list">
                        <li>Sin descargar ninguna app</li>
                        <li>Compatible con iOS y Android</li>
                        <li>Registro en menos de 10 segundos</li>
                    </ul>
                </div>
            </article>

            <article class="step-detailed">
                <div class="step-image">
                    <img src="{{ asset('images/big/comerciante-envia-notificacion-panel-eventify.png') }}"
                         alt="Comerciante enviando notificación push desde el panel Eventify"
                         width="500" height="360" loading="lazy">
                </div>
                <div class="step-content">
                    <span class="step-badge">Paso 3</span>
                    <h2>Envía ofertas, ellos vuelven</h2>
                    <p>Desde tu panel envías notificaciones push a todos tus clientes registrados. Las reciben directamente en la pantalla del móvil — sin depender del algoritmo de redes sociales.</p>
                    <ul class="step-list">
                        <li>Notificaciones push instantáneas</li>
                        <li>Segmentación por fecha, tipo de cliente</li>
                        <li>Estadísticas de aperturas y visitas</li>
                    </ul>
                </div>
            </article>

        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="section" style="background:#f9fafb">
    <div class="container">
        <h2 class="section-title" style="text-align:center">Preguntas frecuentes</h2>
        <div class="faq-list">
            <details class="faq-item">
                <summary>¿Cuánto cuesta Eventify?</summary>
                <p>Eventify tiene un plan gratuito con el que puedes empezar sin coste. Los planes de pago desbloquean más clientes, envíos ilimitados y funciones avanzadas de segmentación.</p>
            </details>
            <details class="faq-item">
                <summary>¿Los clientes necesitan instalar una app?</summary>
                <p>No. Los clientes solo necesitan la cámara de su móvil para escanear el QR. No hay nada que descargar ni instalar.</p>
            </details>
            <details class="faq-item">
                <summary>¿Puedo usar Eventify si tengo varios locales?</summary>
                <p>Sí. Cada local tiene su propio QR y panel. Desde la misma cuenta puedes gestionar todos tus establecimientos.</p>
            </details>
            <details class="faq-item">
                <summary>¿Cómo reciben las notificaciones los clientes?</summary>
                <p>Como notificaciones push del navegador, directamente en la pantalla de su móvil o escritorio. Sin necesidad de que estén dentro de ninguna web.</p>
            </details>
        </div>

        <div style="text-align:center;margin-top:3rem">
            <a href="{{ $appUrl }}/qr?source=como-funciona" class="btn btn-primary btn-lg">Empezar gratis ahora</a>
        </div>
    </div>
</section>

@endsection

@push('head')
<style>
.steps-detailed { display: flex; flex-direction: column; gap: 5rem; }
.step-detailed { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; }
.step-detailed.step-reversed { direction: rtl; }
.step-detailed.step-reversed > * { direction: ltr; }
.step-image img { border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,.12); width: 100%; height: auto; }
.step-badge { display: inline-block; background: #e8deff; color: #6c3fc5; font-size: 0.8rem; font-weight: 700; padding: 0.3rem 0.9rem; border-radius: 99px; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: .05em; }
.step-content h2 { font-size: 1.6rem; margin-bottom: 1rem; }
.step-content p { color: #4b5563; margin-bottom: 1.25rem; }
.step-list { display: flex; flex-direction: column; gap: 0.5rem; }
.step-list li { display: flex; align-items: center; gap: 0.5rem; font-size: 0.95rem; color: #374151; }
.step-list li::before { content: '✓'; color: #6c3fc5; font-weight: 700; }
.faq-list { max-width: 720px; margin: 2rem auto 0; display: flex; flex-direction: column; gap: 1rem; }
.faq-item { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
.faq-item summary { padding: 1.25rem 1.5rem; font-weight: 600; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; }
.faq-item summary::after { content: '+'; font-size: 1.25rem; color: #6c3fc5; }
.faq-item[open] summary::after { content: '−'; }
.faq-item p { padding: 0 1.5rem 1.25rem; color: #4b5563; }
.btn-lg { padding: 1rem 2.5rem; font-size: 1.1rem; }
@media(max-width:768px){
    .step-detailed { grid-template-columns: 1fr; }
    .step-detailed.step-reversed { direction: ltr; }
    .step-image { order: -1; }
}
</style>
@endpush
