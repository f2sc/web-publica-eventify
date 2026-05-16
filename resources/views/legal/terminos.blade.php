@extends('layouts.app')
@section('content')
<div class="sub-hero" style="min-height:220px;">
    <div class="sub-hero-bg" style="background:var(--grad-brand);"></div>
    <div class="sub-hero-ov"></div>
    <div class="sub-hero-cnt">
        <div class="sub-ey">Legal</div>
        <h1>Términos y condiciones</h1>
    </div>
</div>
<section class="section">
    <div class="container" style="max-width:780px;">
        <div class="legal-body">

            <p class="legal-updated">Última actualización: {{ date('d/m/Y') }}</p>

            <h2>1. Objeto</h2>
            <p>Los presentes términos regulan el acceso y uso de la plataforma Eventify por parte de los comercios (usuarios B2B) y de los clientes finales que se registran a través de los códigos QR.</p>

            <h2>2. Alta y uso del servicio</h2>
            <p>El comercio se da de alta en <strong>app.eventify.es</strong> proporcionando información veraz sobre su negocio. El comercio es responsable de la custodia de sus credenciales y del uso que haga de la plataforma.</p>

            <h2>3. Plan gratuito y de pago</h2>
            <p>Eventify ofrece un plan gratuito con límites de uso y planes de pago que amplían las funcionalidades. Los precios vigentes en cada momento están publicados en <strong>eventify.es/precios</strong>. El comercio puede cancelar su suscripción en cualquier momento sin penalización.</p>

            <h2>4. Uso aceptable</h2>
            <p>El comercio se compromete a no usar la plataforma para enviar comunicaciones no solicitadas (spam), contenido ilegal, engañoso o contrario a la normativa vigente. Eventify se reserva el derecho de suspender cuentas que incumplan estas condiciones.</p>

            <h2>5. Propiedad intelectual</h2>
            <p>El software, diseño y contenidos de Eventify son propiedad exclusiva de Eventify. El comercio conserva la propiedad de sus datos de clientes y su contenido.</p>

            <h2>6. Limitación de responsabilidad</h2>
            <p>Eventify no garantiza la disponibilidad ininterrumpida del servicio. En ningún caso la responsabilidad de Eventify superará el importe abonado por el comercio en los últimos 3 meses.</p>

            <h2>7. Legislación aplicable</h2>
            <p>Estos términos se rigen por la legislación española. Para cualquier controversia, las partes se someten a los juzgados y tribunales de Madrid.</p>

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
