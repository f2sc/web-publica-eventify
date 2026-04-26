@extends('layouts.app')

@section('content')

<section class="hero">
    <div class="hero-container">
        <span class="hero-badge">Para comercios</span>
        <h1>Consigue que tus clientes vuelvan.</h1>
        <p>Con Eventify, cada cliente que entra puede convertirse en un cliente fiel. Sin app, sin tarjetas, sin complicaciones.</p>
        <div class="hero-ctas">
            <a href="{{ $appUrl }}/qr?source=para-comercios&tipo=general" class="btn btn-accent">Crear mi QR gratis</a>
            <a href="{{ url('/como-funciona') }}" class="btn btn-secondary" style="color:#fff;border-color:rgba(255,255,255,.5)">Cómo funciona</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div style="text-align:center;margin-bottom:3rem">
            <h2 class="section-title">¿Por qué los comercios eligen Eventify?</h2>
        </div>
        <div class="features-grid">
            <article class="feature-card">
                <div class="feature-icon">📲</div>
                <h3>Captación instantánea</h3>
                <p>El cliente escanea el QR y en 10 segundos está registrado. Sin app, sin fricciones. Tu base de datos crece sola.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon">🔔</div>
                <h3>Notificaciones directas</h3>
                <p>Llega al móvil de tus clientes cuando publicas una oferta o novedad. Sin algoritmos que filtren tu mensaje.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Estadísticas en tiempo real</h3>
                <p>Sabe cuántos clientes tienes, cuándo fueron la última vez y qué campañas funcionan mejor.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon">🤝</div>
                <h3>Red local colaborativa</h3>
                <p>Comparte clientes con comercios de tu zona y benefíciate de la red de tu asociación de comerciantes.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon">🎯</div>
                <h3>Segmentación precisa</h3>
                <p>Envía ofertas solo a clientes que no han visitado en 30 días, o a los más fieles. Tú decides.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon">💶</div>
                <h3>Asequible desde el día 1</h3>
                <p>Plan gratuito para empezar. Sin permanencia, sin comisiones. Pagas solo si creces.</p>
            </article>
        </div>
    </div>
</section>

<section class="section" style="background:#f9fafb">
    <div class="container" style="text-align:center">
        <h2 class="section-title">Empieza hoy mismo</h2>
        <p class="section-subtitle" style="margin:0 auto 2.5rem">Sin tarjeta de crédito. En 2 minutos tienes tu QR listo.</p>
        <a href="{{ $appUrl }}/qr?source=para-comercios-cta" class="btn btn-primary" style="font-size:1.1rem;padding:1rem 2.5rem">Crear mi QR gratis →</a>
    </div>
</section>

@endsection

@push('head')
<style>
.features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
.feature-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.75rem; }
.feature-icon { font-size: 2rem; margin-bottom: 1rem; }
.feature-card h3 { font-size: 1.05rem; margin-bottom: 0.5rem; }
.feature-card p { color: #6b7280; font-size: 0.9rem; }
@media(max-width:768px){ .features-grid { grid-template-columns: 1fr; } }
@media(max-width:480px){ .features-grid { grid-template-columns: 1fr; } }
</style>
@endpush
