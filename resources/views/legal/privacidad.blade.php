@extends('layouts.app')
@section('content')
<div class="sub-hero" style="min-height:220px;">
    <div class="sub-hero-bg" style="background:var(--grad-brand);"></div>
    <div class="sub-hero-ov"></div>
    <div class="sub-hero-cnt">
        <div class="sub-ey">Legal</div>
        <h1>Política de privacidad</h1>
    </div>
</div>
<section class="section">
    <div class="container" style="max-width:780px;">
        <div class="legal-body">

            <p class="legal-updated">Última actualización: {{ date('d/m/Y') }}</p>

            <h2>1. Responsable del tratamiento</h2>
            <p><strong>Eventify</strong> (en adelante, "Eventify" o "la Plataforma") es el responsable del tratamiento de los datos personales recogidos a través de este sitio web y de la aplicación <strong>app.eventify.es</strong>.</p>

            <h2>2. Datos que recogemos</h2>
            <p>Cuando un cliente escanea el código QR de un comercio y se registra, recogemos:</p>
            <ul>
                <li>Nombre y apellidos</li>
                <li>Número de teléfono móvil</li>
                <li>Dirección de correo electrónico (opcional)</li>
                <li>Fecha y hora del registro</li>
            </ul>

            <h2>3. Finalidad del tratamiento</h2>
            <p>Los datos se utilizan exclusivamente para:</p>
            <ul>
                <li>Gestionar la relación de fidelización entre el cliente y el comercio registrado</li>
                <li>Enviar notificaciones push y emails de campañas del comercio al que el cliente se suscribió</li>
                <li>Generar estadísticas agregadas y anónimas de uso para el comercio</li>
            </ul>

            <h2>4. Base legal</h2>
            <p>El tratamiento se basa en el <strong>consentimiento explícito</strong> del interesado, otorgado en el momento del registro. El cliente puede retirar su consentimiento en cualquier momento contactando con el comercio o con Eventify.</p>

            <h2>5. Cesión de datos</h2>
            <p>Los datos <strong>no se ceden a terceros</strong> salvo obligación legal. El comercio al que el cliente se registró tiene acceso a sus datos de contacto únicamente para las finalidades descritas.</p>

            <h2>6. Derechos del interesado</h2>
            <p>Puedes ejercer tus derechos de acceso, rectificación, supresión, oposición y portabilidad escribiendo a <strong>privacidad@eventify.es</strong>.</p>

            <h2>7. Conservación de datos</h2>
            <p>Los datos se conservan mientras el cliente esté registrado en al menos un comercio. Al darse de baja, se eliminan en un plazo máximo de 30 días.</p>

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
