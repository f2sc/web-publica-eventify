@extends('layouts.app')
@section('content')
<div class="sub-hero" style="min-height:220px;">
    <div class="sub-hero-bg" style="background:var(--grad-brand);"></div>
    <div class="sub-hero-ov"></div>
    <div class="sub-hero-cnt">
        <div class="sub-ey">Legal</div>
        <h1>Política de cookies</h1>
    </div>
</div>
<section class="section">
    <div class="container" style="max-width:780px;">
        <div class="legal-body">

            <p class="legal-updated">Última actualización: {{ date('d/m/Y') }}</p>

            <h2>¿Qué son las cookies?</h2>
            <p>Las cookies son pequeños archivos de texto que los sitios web almacenan en tu navegador para recordar preferencias o analizar el uso del sitio.</p>

            <h2>Cookies que utilizamos</h2>
            <p>Este sitio web utiliza únicamente cookies técnicas imprescindibles para el funcionamiento de la plataforma:</p>
            <ul>
                <li><strong>Sesión (XSRF-TOKEN, laravel_session)</strong> — necesarias para la seguridad del sitio. No recogen datos personales. Se eliminan al cerrar el navegador.</li>
            </ul>
            <p>No utilizamos cookies de publicidad ni de seguimiento de terceros.</p>

            <h2>Cómo gestionar las cookies</h2>
            <p>Puedes configurar tu navegador para bloquear o eliminar cookies. Ten en cuenta que bloquear las cookies técnicas puede afectar al funcionamiento del sitio. Consulta la ayuda de tu navegador:</p>
            <ul>
                <li>Chrome: Configuración → Privacidad → Cookies</li>
                <li>Firefox: Opciones → Privacidad y Seguridad → Cookies</li>
                <li>Safari: Preferencias → Privacidad</li>
            </ul>

            <h2>Contacto</h2>
            <p>Para cualquier consulta sobre cookies, escríbenos a <strong>privacidad@eventify.es</strong>.</p>

        </div>
    </div>
</section>
@endsection

@push('head')
<style>
.legal-body h2 { font-size: 1.15rem; font-weight: 800; color: var(--navy); margin: 2rem 0 0.5rem; }
.legal-body p, .legal-body li { color: #4b5563; font-size: 0.95rem; line-height: 1.75; }
.legal-body ul { padding-left: 1.5rem; margin: 0.5rem 0 1rem; display: flex; flex-direction: column; gap: 0.3rem; list-style: disc; }
.legal-updated { font-size: 0.85rem; color: var(--muted); margin-bottom: 2rem; }
</style>
@endpush
