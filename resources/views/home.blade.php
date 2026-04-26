@extends('layouts.app')

@section('content')

{{-- HERO SPLIT --}}
<section class="hero-split">
    <div class="hero-left">
        <div class="hero-left-inner">
            <div class="hero-badge">
                <span class="badge-dot"></span>
                +1.200 comercios activos en España
            </div>
            <h1>Tu barrio<br>está esperando<br><mark>tu próxima oferta.</mark></h1>
            <p class="hero-sub">Un QR en tu escaparate. Tus clientes se registran con el móvil. Tú les envías notificaciones que de verdad abren. Sin apps. Sin complicaciones.</p>
            <div class="hero-actions">
                <a href="{{ $appUrl }}/qr?source=web-hero" class="btn btn-accent">&#x25B6;&nbsp; Crear mi QR gratis</a>
                <a href="{{ url('/como-funciona') }}" class="btn" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.88);border:1.5px solid rgba(255,255,255,.28);">Ver cómo funciona</a>
            </div>
            <div class="hero-stats">
                <div class="hstat">
                    <div class="hstat-num">+1.200</div>
                    <div class="hstat-lbl">Comercios activos</div>
                </div>
                <div class="hstat">
                    <div class="hstat-num">48.000</div>
                    <div class="hstat-lbl">Clientes captados</div>
                </div>
                <div class="hstat">
                    <div class="hstat-num">23%</div>
                    <div class="hstat-lbl">Apertura media</div>
                </div>
            </div>
        </div>
    </div>

    <div class="hero-right">
        <img class="hero-right-img"
             src="{{ asset('images/big/panadero-cliente-escanea-qr-eventify.png') }}"
             alt="Cliente escaneando QR en un comercio local con Eventify">

        <div class="hero-float hero-float-notif">
            <div class="notif-header">
                <div class="notif-icon">&#x1F514;</div>
                <div class="notif-app">Eventify</div>
                <div class="notif-time">ahora</div>
            </div>
            <div class="notif-text">&#x2615; 2&#x00D7;1 en café esta tarde</div>
            <div class="notif-sub">Café El Rincón · Coslada</div>
        </div>

        <div class="hero-float hero-float-qr">
            <div class="qr-box">&#x2317;</div>
            <div class="qr-text">
                <strong>Tu QR generado</strong>
                <span>Listo para imprimir</span>
            </div>
        </div>

        <div class="hero-float hero-float-stat">
            <div class="fstat-big">+24</div>
            <div class="fstat-lbl">clientes hoy</div>
        </div>
    </div>
</section>

{{-- TRUST BAR --}}
<div class="trust-bar">
    <div class="trust-inner">
        <span class="trust-lbl">Confían en nosotros:</span>
        <div class="trust-logos">
            <div class="trust-logo">&#x1F3EA; Asoc. Comercio Leganés</div>
            <div class="trust-logo">&#x1F3D9; Ayto. Coslada</div>
            <div class="trust-logo">&#x1F3EA; Comerciantes Getafe</div>
            <div class="trust-logo">&#x1F3D9; Ayto. Fuenlabrada</div>
            <div class="trust-logo">&#x1F3EA; Comercios Badalona</div>
        </div>
    </div>
</div>

{{-- HISTORIAS REALES --}}
<section class="section" style="background:var(--warm);overflow:hidden;">
    <div class="container">
        <div class="eyebrow">Historias reales</div>
        <h2 class="section-title">El barrio que ya usa Eventify</h2>
        <p class="section-subtitle">Comercios como el tuyo que pasaron de cero clientes digitales a cientos de contactos en semanas.</p>

        <div class="stories-grid">
            <div class="story-card">
                <img src="{{ asset('images/big/cafeteria-barrio-fidelizacion-clientes.jpg') }}" alt="Cafetería con fidelización Eventify" loading="lazy">
                <div class="story-overlay"></div>
                <div class="story-content">
                    <div class="story-cat">&#x2615; Cafetería</div>
                    <h3>"En 3 semanas tenía 400 clientes en mi lista"</h3>
                    <p>María puso el QR en la barra. Sus clientes lo escanean esperando el café.</p>
                    <div class="story-meta">
                        <div class="story-avatar">&#x2615;</div>
                        <div class="story-who">María G. — Café El Rincón, Coslada</div>
                    </div>
                </div>
            </div>
            <div class="story-card">
                <img src="{{ asset('images/big/peluqueria-barrio-notificaciones-push.jpg') }}" alt="Peluquería con notificaciones push Eventify" loading="lazy">
                <div class="story-overlay"></div>
                <div class="story-content">
                    <div class="story-cat">&#x2702; Peluquería</div>
                    <h3>"Mis clientes vuelven cuando les mando un descuento"</h3>
                    <p>Carlos envía una oferta flash cada viernes. El lunes llena la agenda.</p>
                    <div class="story-meta">
                        <div class="story-avatar">&#x1F488;</div>
                        <div class="story-who">Carlos R. — Barber House, Getafe</div>
                    </div>
                </div>
            </div>
            <div class="story-card">
                <img src="{{ asset('images/big/restaurante-local-captacion-clientes.jpg') }}" alt="Restaurante local con Eventify" loading="lazy">
                <div class="story-overlay"></div>
                <div class="story-content">
                    <div class="story-cat">&#x1F374; Restaurante</div>
                    <h3>"El ayuntamiento nos puso de ejemplo digital"</h3>
                    <p>La asociación se apuntó completa. 18 comercios, una sola herramienta.</p>
                    <div class="story-meta">
                        <div class="story-avatar">&#x1F3DB;</div>
                        <div class="story-who">Ana M. — Asoc. Comercio Leganés</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ECOSISTEMA --}}
<div class="eco-sec">
    <div class="eco-header">
        <div class="eyebrow">El ecosistema</div>
        <h2 class="section-title">Comercios, asociación<br>y clientes. Todos conectados.</h2>
        <p class="section-subtitle" style="margin:0 auto 2rem;">Un solo QR activa toda la red. La asociación coordina, los comercios captan clientes y las personas reciben ofertas de su barrio en tiempo real.</p>
        <div class="eco-pills">
            <div class="eco-pill">&#x1F3EA; Comercios locales</div>
            <div class="eco-pill">&#x1F3DB; Asociaciones y ayuntamientos</div>
            <div class="eco-pill">&#x1F465; Clientes del barrio</div>
        </div>
    </div>
    <div class="eco-img-wrap">
        <img src="{{ asset('images/big/ecosistema-comercio-local-asociacion-clientes-eventify.png') }}"
             alt="Ecosistema Eventify: asociación, comercios y clientes conectados" loading="lazy">
        <div class="eco-overlay"></div>
    </div>
</div>

{{-- CÓMO FUNCIONA --}}
<section class="section" style="background:#fff;">
    <div class="container">
        <div class="eyebrow">El proceso</div>
        <h2 class="section-title">Tres pasos. Hoy mismo.</h2>
        <p class="section-subtitle">No necesitas saber de tecnología. Si sabes enviar un WhatsApp, sabes usar Eventify.</p>

        <div class="how-timeline">
            <div class="how-row">
                <div class="how-copy">
                    <div class="how-step-num">01</div>
                    <h3>Crea tu QR en 5 minutos</h3>
                    <p>Regístrate, pon el nombre de tu negocio y descarga tu cartel con el QR. Listo para imprimir hoy mismo y colocarlo en tu escaparate o mostrador.</p>
                    <div class="how-detail">
                        <div class="how-detail-item"><div class="how-detail-icon">&#x2713;</div>Sin conocimientos técnicos</div>
                        <div class="how-detail-item"><div class="how-detail-icon">&#x2713;</div>Cartel listo para imprimir</div>
                        <div class="how-detail-item"><div class="how-detail-icon">&#x2713;</div>100% gratuito para empezar</div>
                    </div>
                </div>
                <div class="how-img">
                    <img src="{{ asset('images/big/comerciante-panel-eventify-cartel-qr-tienda.png') }}"
                         alt="Comerciante usando el panel Eventify con cartel QR" loading="lazy">
                </div>
            </div>

            <div class="how-row">
                <div class="how-img">
                    <img src="{{ asset('images/big/clientes-escanean-qr-bar-eventify.png') }}"
                         alt="Clientes escaneando QR Eventify en un bar" loading="lazy">
                </div>
                <div class="how-copy">
                    <div class="how-step-num">02</div>
                    <h3>Tus clientes se registran solos</h3>
                    <p>Escanean el QR con el móvil, dejan su nombre y móvil y en 30 segundos están en tu base de datos. Sin instalar ninguna app.</p>
                    <div class="how-detail">
                        <div class="how-detail-item"><div class="how-detail-icon">&#x2713;</div>Sin descargar ninguna app</div>
                        <div class="how-detail-item"><div class="how-detail-icon">&#x2713;</div>Consentimiento RGPD incluido</div>
                        <div class="how-detail-item"><div class="how-detail-icon">&#x2713;</div>Ves cada registro en tiempo real</div>
                    </div>
                </div>
            </div>

            <div class="how-row">
                <div class="how-copy">
                    <div class="how-step-num">03</div>
                    <h3>Envía notificaciones que abren</h3>
                    <p>Escribes el mensaje, eliges a quién va y le das a enviar. Tus clientes lo reciben en el móvil. El 23% lo abre. Ninguna red social llega a eso.</p>
                    <div class="how-detail">
                        <div class="how-detail-item"><div class="how-detail-icon">&#x2713;</div>23% de tasa de apertura media</div>
                        <div class="how-detail-item"><div class="how-detail-icon">&#x2713;</div>Lista en menos de 2 minutos</div>
                        <div class="how-detail-item"><div class="how-detail-icon">&#x2713;</div>Push + email en un solo envío</div>
                    </div>
                </div>
                <div class="how-img">
                    <img src="{{ asset('images/big/comerciante-envia-notificacion-panel-eventify.png') }}"
                         alt="Comerciante enviando notificación push desde Eventify" loading="lazy">
                </div>
            </div>
        </div>
    </div>
</section>

{{-- BENEFICIOS --}}
<section class="section" style="background:var(--surface);">
    <div class="container">
        <div style="text-align:center;margin-bottom:3rem;">
            <div class="eyebrow" style="justify-content:center;">Por qué funciona</div>
            <h2 class="section-title">Todo lo que necesita tu negocio</h2>
        </div>
        <div class="bene-grid">
            <div class="bene">
                <div class="bene-icon">&#x1F4F2;</div>
                <h3>Captación sin fricciones</h3>
                <p>El cliente se registra en 10 segundos con solo su nombre y teléfono. Tu base de datos crece sola.</p>
            </div>
            <div class="bene feat">
                <div class="bene-icon">&#x1F514;</div>
                <h3>Notificaciones directas</h3>
                <p>Llega al móvil de tus clientes cuando publicas una oferta. Sin algoritmos que filtren tu mensaje.</p>
            </div>
            <div class="bene">
                <div class="bene-icon">&#x1F4CA;</div>
                <h3>Panel en tiempo real</h3>
                <p>Sabe cuántos clientes tienes, cuándo fueron la última vez y qué campañas funcionan mejor.</p>
            </div>
            <div class="bene">
                <div class="bene-icon">&#x1F91D;</div>
                <h3>Red local colaborativa</h3>
                <p>Comparte clientes con comercios de tu zona y benefíciate de la red de tu asociación.</p>
            </div>
            <div class="bene">
                <div class="bene-icon">&#x1F3AF;</div>
                <h3>Segmentación precisa</h3>
                <p>Envía ofertas solo a clientes que no han visitado en 30 días, o a los más fieles. Tú decides.</p>
            </div>
            <div class="bene">
                <div class="bene-icon">&#x1F4B6;</div>
                <h3>Asequible desde el día 1</h3>
                <p>Plan gratuito para empezar. Sin permanencia, sin comisiones. Pagas solo si creces.</p>
            </div>
        </div>
    </div>
</section>

{{-- LOCALIDADES --}}
@if(count($localidades) > 0)
<div class="loc-dark">
    <div class="container">
        <div class="eyebrow">Dónde estamos</div>
        <h2 class="section-title">Comercios adheridos en tu zona</h2>
        <p class="section-subtitle">Localidades donde ya usamos Eventify. ¿Está la tuya?</p>
    </div>
    <div class="marquee-wrap" style="margin-top:2.75rem;">
        <div class="marquee-track">
            @foreach($localidades as $loc)
            <a href="{{ url('/localidades/' . ($loc['slug'] ?? '')) }}" class="loc-chip">
                {{ $loc['nombre'] ?? $loc['name'] ?? '' }}
                @if(isset($loc['num_comercios']))
                    <span class="loc-chip-count">{{ $loc['num_comercios'] }}</span>
                @endif
            </a>
            @endforeach
            {{-- Duplicar para loop infinito --}}
            @foreach($localidades as $loc)
            <a href="{{ url('/localidades/' . ($loc['slug'] ?? '')) }}" class="loc-chip" aria-hidden="true">
                {{ $loc['nombre'] ?? $loc['name'] ?? '' }}
                @if(isset($loc['num_comercios']))
                    <span class="loc-chip-count">{{ $loc['num_comercios'] }}</span>
                @endif
            </a>
            @endforeach
        </div>
    </div>
    <div class="container" style="margin-top:2.5rem;text-align:center;">
        <a href="{{ url('/localidades') }}" class="btn btn-secondary" style="color:rgba(255,255,255,.8);border-color:rgba(255,255,255,.3);">Ver todas las localidades</a>
    </div>
</div>
@endif

{{-- CTA FINAL SPLIT --}}
<div class="cta-final-split">
    <div class="cta-left">
        <div class="cta-left-inner">
            <h2>¿Listo para fidelizar a tus clientes?</h2>
            <p>Regístrate gratis en 2 minutos. Sin tarjeta de crédito. Sin permanencia.</p>
            <div class="cta-btns">
                <a href="{{ $appUrl }}/qr?source=web-cta-final" class="btn btn-accent">Crear mi QR gratis</a>
                <a href="{{ url('/como-funciona') }}" class="btn" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.8);border:1.5px solid rgba(255,255,255,.25);">Cómo funciona</a>
            </div>
        </div>
    </div>
    <div class="cta-right">
        <img src="{{ asset('images/big/comerciante-movil-tienda-local-eventify.png') }}"
             alt="Comerciante gestionando Eventify desde su móvil" loading="lazy">
        <div class="cta-right-ov"></div>
    </div>
</div>

@endsection
