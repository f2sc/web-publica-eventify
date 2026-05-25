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

            <h3 style="font-size:1rem;font-weight:700;color:var(--navy);margin:1.5rem 0 .4rem;">Cookies técnicas (siempre activas)</h3>
            <p>Imprescindibles para el funcionamiento del sitio. No requieren consentimiento.</p>
            <ul>
                <li><strong>XSRF-TOKEN, laravel_session</strong> — seguridad y sesión. Se eliminan al cerrar el navegador.</li>
            </ul>

            <h3 style="font-size:1rem;font-weight:700;color:var(--navy);margin:1.5rem 0 .4rem;">Cookies analíticas (requieren consentimiento)</h3>
            <p>Solo se activan si aceptas desde el aviso de cookies. Nos ayudan a entender cómo se usa la web para mejorarla.</p>
            <ul>
                <li><strong>Google Analytics 4</strong> (_ga, _ga_*) — mide visitas, páginas vistas y origen del tráfico de forma anonimizada. Los datos se almacenan en servidores de Google. <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Política de privacidad de Google</a>.</li>
            </ul>
            <p>No utilizamos cookies de publicidad ni de remarketing.</p>

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
