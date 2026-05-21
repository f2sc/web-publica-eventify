@extends('layouts.app')

@section('content')

{{-- HERO SPLIT --}}
<section class="hero-split">
    <div class="hero-left">
        <div class="hero-left-inner">
            <div class="hero-badge">
                <span class="badge-dot"></span>
                {{ isset($stats['comercios']) ? $stats['comercios'] . ' comercios' : 'Comercios' }} activos en España
            </div>
            <h1>Tu barrio<br>está esperando<br><mark>tu próxima oferta.</mark></h1>
            <p class="hero-sub">Un QR en tu escaparate. Tus clientes se registran con el móvil. Tú les envías notificaciones que de verdad abren. Sin apps. Sin complicaciones.</p>
            <div class="hero-actions">
                <a href="{{ $appUrl }}/register?source=web-hero" class="btn btn-accent">&#x25B6;&nbsp; Registrarme gratis</a>
                <a href="{{ url('/como-funciona') }}" class="btn" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.88);border:1.5px solid rgba(255,255,255,.28);">Ver cómo funciona</a>
            </div>
            @if(!empty($stats))
            <div class="hero-stats">
                @if(isset($stats['comercios']))
                <div class="hstat">
                    <div class="hstat-num">{{ number_format($stats['comercios']) }}</div>
                    <div class="hstat-lbl">Comercios activos</div>
                </div>
                @endif
                @if(isset($stats['clientes']))
                <div class="hstat">
                    <div class="hstat-num">{{ number_format($stats['clientes']) }}</div>
                    <div class="hstat-lbl">Clientes registrados</div>
                </div>
                @endif
                @if(isset($stats['localidades']))
                <div class="hstat">
                    <div class="hstat-num">{{ number_format($stats['localidades']) }}</div>
                    <div class="hstat-lbl">{{ $stats['localidades'] == 1 ? 'Localidad activa' : 'Localidades activas' }}</div>
                </div>
                @endif
            </div>
            @endif
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
            @forelse($asociaciones as $asoc)
            <div class="trust-logo">&#x1F3DB; {{ $asoc['nombre'] }}{{ isset($asoc['localidad']) ? ' · ' . $asoc['localidad'] : '' }}</div>
            @empty
            <div class="trust-logo">&#x1F3EA; Asoc. Comercio Leganés</div>
            <div class="trust-logo">&#x1F3D9; Ayto. Coslada</div>
            <div class="trust-logo">&#x1F3EA; Comerciantes Getafe</div>
            <div class="trust-logo">&#x1F3D9; Ayto. Fuenlabrada</div>
            <div class="trust-logo">&#x1F3EA; Comercios Badalona</div>
            @endforelse
        </div>
    </div>
</div>

{{-- HISTORIAS REALES --}}
{{-- <section class="section" style="background:var(--warm);overflow:hidden;"> --}}
@if(false)
    <div class="container">
        <div class="eyebrow">Lo que dicen</div>
        <h2 class="section-title">Así lo están viviendo</h2>
        <p class="section-subtitle">Comercios que pasaron de cero clientes digitales a cientos de contactos en semanas.</p>

        @php
        $stories = [
            [
                'img'   => asset('images/big/cafeteria-barrio-fidelizacion-clientes.jpg'),
                'cat'   => '☕ Cafetería',
                'quote' => '"En 3 semanas tenía 400 clientes en mi lista"',
                'desc'  => 'Puso el QR en el mostrador. Sus clientes lo escanean mientras esperan el pedido.',
                'icon'  => '☕',
                'name'  => 'María G.',
                'role'  => 'Cafetería · Madrid',
            ],
            [
                'img'   => asset('images/big/peluqueria-barrio-notificaciones-push.jpg'),
                'cat'   => '✂️ Peluquería',
                'quote' => '"Mis clientes vuelven cuando les mando un descuento"',
                'desc'  => 'Envía una oferta flash cada semana. Llena la agenda sin esfuerzo.',
                'icon'  => '✂️',
                'name'  => 'Carlos R.',
                'role'  => 'Barbería · Getafe',
            ],
            [
                'img'   => asset('images/big/restaurante-local-captacion-clientes.jpg'),
                'cat'   => '🏛 Asociación',
                'quote' => '"El ayuntamiento nos puso de ejemplo digital"',
                'desc'  => 'La asociación se apuntó completa. Muchos comercios, una sola herramienta.',
                'icon'  => '🏛',
                'name'  => 'Pedro S.',
                'role'  => 'Asociación · Fuenlabrada',
            ],
        ];
        @endphp
        <div class="stories-grid">
            @foreach($stories as $s)
            <div class="story-card">
                <img src="{{ $s['img'] }}" alt="Comercio local usando Eventify" loading="lazy">
                <div class="story-overlay"></div>
                <div class="story-content">
                    <div class="story-cat">{{ $s['cat'] }}</div>
                    <h3>{{ $s['quote'] }}</h3>
                    <p>{{ $s['desc'] }}</p>
                    <div class="story-meta">
                        <div class="story-avatar">{{ $s['icon'] }}</div>
                        <div class="story-who">{{ $s['name'] }} &mdash; {{ $s['role'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
@endif
{{-- </section> --}}

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
                    <p>Escanean el QR con el móvil, dejan su nombre y móvil y en 30 segundos están registrados en Eventify. Sin instalar ninguna app.</p>
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

{{-- ESCAPARATE DIGITAL --}}
<section class="pagcom-sec">
    <div class="pagcom-inner">
        <div class="pagcom-copy">
            <div class="eyebrow">&#x1F3EA; Tu escaparate digital</div>
            <h2 class="section-title">Al registrarte, tu página de comercio se crea sola</h2>
            <p class="section-subtitle">Sin diseñadores, sin webs de terceros. Solo pon el nombre y logotipo de tu comercio y tendrás una página pública lista para compartir.</p>
            <div class="pagcom-features">
                <div class="pagcom-feat">
                    <div class="pagcom-feat-ico">&#x1F381;</div>
                    <div class="pagcom-feat-txt">
                        <strong>Ofertas en tiempo real</strong>
                        <span>Tus clientes ven siempre tus promociones activas.</span>
                    </div>
                </div>
                <div class="pagcom-feat">
                    <div class="pagcom-feat-ico">&#x2B50;</div>
                    <div class="pagcom-feat-txt">
                        <strong>Fidelización integrada</strong>
                        <span>El programa de puntos aparece directamente en tu página.</span>
                    </div>
                </div>
                <div class="pagcom-feat">
                    <div class="pagcom-feat-ico">&#x1F5BC;</div>
                    <div class="pagcom-feat-txt">
                        <strong>Galería de productos</strong>
                        <span>Muestra tus platos, productos o servicios estrella.</span>
                    </div>
                </div>
                <div class="pagcom-feat">
                    <div class="pagcom-feat-ico">&#x1F31F;</div>
                    <div class="pagcom-feat-txt">
                        <strong>Reseñas Google</strong>
                        <span>Tus valoraciones de Google aparecen automáticamente.</span>
                    </div>
                </div>
                <div class="pagcom-feat">
                    <div class="pagcom-feat-ico">&#x1F4CD;</div>
                    <div class="pagcom-feat-txt">
                        <strong>Mapa y horarios</strong>
                        <span>Dirección, teléfono y horario siempre visibles.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pagcom-visual">
            <div class="pagcom-browser">
                <div class="pagcom-browser-bar">
                    <span class="pagcom-dot" style="background:#ff5f57"></span>
                    <span class="pagcom-dot" style="background:#febc2e"></span>
                    <span class="pagcom-dot" style="background:#28c840"></span>
                    <span class="pagcom-url">app.eventify.es/comercio/cafe-el-rincon</span>
                </div>
                <div class="pagcom-screen">
                    <div class="pc2-hero-img">
                        <img src="{{ asset('images/big/cafeteria-barrio-fidelizacion-clientes.jpg') }}"
                             alt="Página de comercio en Eventify" loading="lazy"
                             style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
                    </div>
                    <div class="pc2-card" style="display:flex;align-items:center;gap:10px;">
                        <div class="pc2-logo">&#x2615;</div>
                        <div>
                            <strong style="display:block;font-size:13px;">Café El Rincón</strong>
                            <span class="pc2-pill" style="background:#f3f4f6;">&#x2615; Cafetería &middot; Coslada</span>
                        </div>
                    </div>
                    <div class="pc2-nav">
                        <span class="pc2-pill" style="background:var(--brand);color:#fff;">Ofertas</span>
                        <span class="pc2-pill">Fidelización</span>
                        <span class="pc2-pill">Galería</span>
                        <span class="pc2-pill">Info</span>
                    </div>
                    <div class="pc2-sec">
                        <strong style="font-size:.65rem;color:#374151;">Ofertas activas</strong>
                        <div class="pc2-offers">
                            <div class="pc2-offer-card">
                                <div style="font-size:.75rem;font-weight:700;">&#x2615; 2&times;1 en café</div>
                                <div style="font-size:.6rem;color:#6b7280;">Hoy hasta las 11h</div>
                            </div>
                            <div class="pc2-offer-card">
                                <div style="font-size:.75rem;font-weight:700;">&#x1F950; Desayuno -20%</div>
                                <div style="font-size:.6rem;color:#6b7280;">Nuevos clientes</div>
                            </div>
                        </div>
                    </div>
                    <div class="pc2-loyalty" style="background:var(--grad-brand);">
                        <div class="pc2-loyalty-pat"></div>
                        <strong style="font-size:.65rem;color:rgba(255,255,255,.8);display:block;margin-bottom:8px;">Tu fidelización</strong>
                        <div class="pc2-stamps-grid">
                            <div class="pc2-stamp filled">&#x2713;</div>
                            <div class="pc2-stamp filled">&#x2713;</div>
                            <div class="pc2-stamp filled">&#x2713;</div>
                            <div class="pc2-stamp">4</div>
                            <div class="pc2-stamp">5</div>
                        </div>
                        <div class="pc2-progress">
                            <div class="pc2-progress-bar" style="width:60%"></div>
                        </div>
                        <div class="pc2-loyalty-foot" style="color:rgba(255,255,255,.7);font-size:.65rem;">3 de 5 visitas &rarr; caf&eacute; gratis</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- BENEFICIOS CON TABS --}}
<section class="section" style="background:var(--surface);">
    <div class="container">
        <div style="text-align:center;margin-bottom:3rem;">
            <div class="eyebrow" style="justify-content:center;">Para todos</div>
            <h2 class="section-title">Un ecosistema para todo el barrio</h2>
        </div>
        <div class="tab-row">
            <button class="tab-btn on" onclick="swTab(this,'tb-c')">&#x1F3EA; Comercios</button>
            <button class="tab-btn" onclick="swTab(this,'tb-cl')">&#x1F465; Clientes</button>
            <button class="tab-btn" onclick="swTab(this,'tb-a')">&#x1F3DB; Asociaciones</button>
        </div>
        <div id="tb-c" class="bene-tab-panel on">
            <div class="bene feat"><div class="bene-icon">&#x1F5C3;</div><h3>Tus clientes, sin intermediarios.</h3><p>Sin depender de Instagram ni de algoritmos. Cada escaneo suma un contacto directo al que puedes llegar siempre.</p></div>
            <div class="bene"><div class="bene-icon">&#x1F3AF;</div><h3>Notificaciones segmentadas</h3><p>Envía solo a quien le importa: por zona, tipo de cliente o fecha de última visita.</p></div>
            <div class="bene"><div class="bene-icon">&#x1F4C8;</div><h3>Analítica real, sin rodeos</h3><p>Cuántos clientes, qué tasa de apertura, qué campañas funcionan. Datos claros para decidir mejor.</p></div>
            <div class="bene"><div class="bene-icon">&#x1F382;</div><h3>Automatizaciones que fidelizan</h3><p>Cumpleaños automáticos, mensajes de bienvenida, recordatorios de visita. Solo se configuran una vez.</p></div>
            <div class="bene"><div class="bene-icon">&#x1F4F1;</div><h3>El cliente no instala nada</h3><p>Solo el móvil y la cámara. Sin barreras. Así se registran el 90% de las personas que escanean.</p></div>
            <div class="bene"><div class="bene-icon">&#x26A1;</div><h3>Panel en español, sin curva</h3><p>Si sabes usar WhatsApp, sabes usar Eventify. Pensado para el dueño, no para un informático.</p></div>
        </div>
        <div id="tb-cl" class="bene-tab-panel">
            <div class="bene feat"><div class="bene-icon">&#x1F381;</div><h3>Las mejores ofertas de tu barrio</h3><p>Recibe solo lo que te interesa, de los comercios que eliges. Sin ruido de grandes superficies.</p></div>
            <div class="bene"><div class="bene-icon">&#x1F6E1;</div><h3>Tú decides siempre</h3><p>Te das de baja de cualquier comercio con un clic. Tú mandas sobre tus notificaciones.</p></div>
            <div class="bene"><div class="bene-icon">&#x2764;</div><h3>Apoya el comercio local</h3><p>Cada escaneo hace crecer el comercio de tu barrio y te da ventajas que los grandes nunca te darán.</p></div>
        </div>
        <div id="tb-a" class="bene-tab-panel">
            <div class="bene feat"><div class="bene-icon">&#x1F3DB;</div><h3>Panel para toda la asociación</h3><p>Gestiona todos tus comercios desde un único lugar. Campañas conjuntas, métricas globales.</p></div>
            <div class="bene"><div class="bene-icon">&#x1F4CA;</div><h3>Datos para el ayuntamiento</h3><p>Informes reales de actividad para justificar vuestra labor y acceder a subvenciones de digitalización.</p></div>
            <div class="bene"><div class="bene-icon">&#x1F3D9;</div><h3>Modelo B2B2G</h3><p>Eventify es la infraestructura digital del comercio de proximidad. Comercios + Asociación + Ayuntamiento.</p></div>
        </div>
    </div>
</section>

{{-- POSTER SECTION --}}
<div class="poster-sec">
    <div class="poster-sec-bg" style="background-image:url('{{ asset('images/big/comerciante-cartel-qr-impreso-eventify.png') }}');"></div>
    <div class="poster-sec-ov"></div>
    <div class="poster-sec-inner">
        <div class="poster-copy">
            <div class="eyebrow">&#x1F4CB; Tu cartel QR</div>
            <h2 class="section-title">De digital a físico<br>en un clic.</h2>
            <p>Eventify genera automáticamente un cartel profesional con tu QR personalizado. Lo descargas, lo imprimes con tu propia impresora y lo pones en el mostrador. Luego animas a cada cliente a escanearlo — así puedes avisarles cuando tengas algo para ellos y conseguir que vuelvan.</p>
            <a href="{{ $appUrl }}/register?source=poster" class="btn btn-accent" style="display:inline-flex;gap:8px;align-items:center;">&#x2B; Regístrate y descarga mi cartel</a>
        </div>
        <div class="poster-imgs">
            <div class="pimg tall"><img src="{{ asset('images/mockup-poster-en-soporte-metacrilato-color.jpg') }}" alt="Cartel QR Eventify en soporte metacrilato" loading="lazy"></div>
            <div class="pimg"><img src="{{ asset('images/mockup-poster-color.jpg') }}" alt="Cartel QR Eventify en color" loading="lazy"></div>
            <div class="pimg"><img src="{{ asset('images/mockup-poster-BYN.jpg') }}" alt="Cartel QR Eventify en blanco y negro" loading="lazy"></div>
        </div>
    </div>
</div>

{{-- LOCALIDADES --}}
@if(count($localidades) > 0)
<div class="loc-dark">
    <div class="container">
        <div class="eyebrow">Cobertura</div>
        <h2 class="section-title">Activo en toda España</h2>
        <p class="section-subtitle">Tu localidad probablemente ya está aquí.</p>
    </div>
    @php
    $staticLocs = ['Getafe','Leganés','Alcorcón','Móstoles','Fuenlabrada','Torrejón de Ardoz','Alcalá de Henares','Hospitalet','Terrassa','Sabadell','Badalona','Mataró','Gijón','Oviedo','Murcia','Alicante','Granada','Valladolid','Zaragoza','Málaga'];
    $realSlugs  = collect($localidades)->pluck('nombre')->map(fn($n) => strtolower($n))->all();
    $extras     = array_filter($staticLocs, fn($n) => !in_array(strtolower($n), $realSlugs));
    @endphp
    <div class="marquee-wrap" style="margin-top:2.75rem;">
        <div class="marquee-track">
            @foreach($localidades as $loc)
            <a href="{{ url('/localidades/' . ($loc['slug'] ?? '')) }}" class="loc-chip loc-chip-real">
                &#x1F4CD; {{ $loc['nombre'] ?? $loc['name'] ?? '' }}
                @if(isset($loc['num_comercios']))
                <span class="loc-chip-count">{{ $loc['num_comercios'] }}</span>
                @endif
            </a>
            @endforeach
            @foreach($extras as $ciudad)
            <span class="loc-chip loc-chip-prox">&#x1F4CD; {{ $ciudad }}</span>
            @endforeach
            {{-- Duplicar para loop infinito --}}
            @foreach($localidades as $loc)
            <a href="{{ url('/localidades/' . ($loc['slug'] ?? '')) }}" class="loc-chip loc-chip-real" aria-hidden="true">
                &#x1F4CD; {{ $loc['nombre'] ?? $loc['name'] ?? '' }}
                @if(isset($loc['num_comercios']))
                <span class="loc-chip-count">{{ $loc['num_comercios'] }}</span>
                @endif
            </a>
            @endforeach
            @foreach($extras as $ciudad)
            <span class="loc-chip loc-chip-prox" aria-hidden="true">&#x1F4CD; {{ $ciudad }}</span>
            @endforeach
        </div>
    </div>
    <div class="container" style="margin-top:2.5rem;text-align:center;">
        <a href="{{ url('/localidades') }}" class="btn btn-secondary" style="color:rgba(255,255,255,.8);border-color:rgba(255,255,255,.3);">Ver todas las localidades</a>
    </div>
</div>
@endif

{{-- TESTIMONIOS --}}
<section class="test-sec">
    <div class="container">
        <div class="eyebrow">Testimonios</div>
        <h2 class="section-title">Comercios que ya lo<br>están notando.</h2>
        <div class="test-featured">
            <div class="tcard-big">
                <div class="test-stars">&#x2605;&#x2605;&#x2605;&#x2605;&#x2605;</div>
                <p>"Antes mandaba mensajes de WhatsApp uno a uno. Ahora en 2 minutos lanzo una campaña a 400 clientes y el mismo día viene gente al local."</p>
                <div class="tauthor" style="display:none">
                    <div class="tavatar">&#x2615;</div>
                    <div>
                        <div class="tname">María González</div>
                        <div class="trole">Café El Rincón &mdash; Coslada</div>
                    </div>
                </div>
            </div>
            <div class="tcard-big">
                <div class="test-stars">&#x2605;&#x2605;&#x2605;&#x2605;&#x2605;</div>
                <p>"Lo del QR parece una tontería pero es lo que más ha sorprendido a mis clientes. 'Qué moderno'. Y ya tengo 280 contactos registrados en Eventify."</p>
                <div class="tauthor" style="display:none">
                    <div class="tavatar">&#x1F488;</div>
                    <div>
                        <div class="tname">Carlos Ruiz</div>
                        <div class="trole">Barber House Pro &mdash; Getafe</div>
                    </div>
                </div>
            </div>
            <div class="tcard-big big2">
                <p>"La tasa de apertura del 23% de media. Mi agencia de marketing me cobraba el triple por peores resultados con email marketing."</p>
                <div class="tauthor" style="display:none">
                    <div class="tavatar">&#x1F33F;</div>
                    <div>
                        <div class="tname">Pedro Sánchez</div>
                        <div class="trole">Herbolario Natura &mdash; Fuenlabrada</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- PRECIOS --}}
<section class="price-sec">
    <div class="container">
        <div style="text-align:center;">
            <div class="eyebrow" style="justify-content:center;">Precios</div>
            <h2 class="section-title">Empieza gratis. Sin sorpresas.</h2>
            <p class="section-subtitle" style="margin:0 auto;">Sin contratos. Sin comisiones. Cancela cuando quieras.</p>
        </div>
        {{-- PLAN FREE DESTACADO --}}
        <div class="price-free-wrap">
            <div class="price-free-card">
                <div class="pfc-left">
                    <div class="pfc-badge">&#x2756; Plan incluido</div>
                    <div class="pfc-name">Gratis</div>
                    <p class="pfc-desc">Todo lo que necesita un comercio local para captar y fidelizar clientes desde el primer d&iacute;a.</p>
                    <ul class="pfc-feats">
                        <li>Clientes ilimitados (1 campa&ntilde;a/d&iacute;a)</li>
                        <li>Campa&ntilde;as ilimitadas</li>
                        <li>Segmentaci&oacute;n avanzada</li>
                        <li>Anal&iacute;tica completa</li>
                        <li>Push + email</li>
                    </ul>
                    <a href="{{ $appUrl }}/register?source=precios-free" class="pfc-btn">Empezar gratis &rarr;</a>
                </div>
                <div class="pfc-right">
                    <div class="pfc-price">0<span>&euro;</span></div>
                    <div class="pfc-per">al mes, siempre</div>
                    <div class="pfc-seal">Sin tarjeta.<br>Sin contratos.</div>
                </div>
            </div>
        </div>

        {{-- ADD-ONS --}}
        <div class="price-addons-wrap">
            <div class="price-addon-sep"><span>&#x2755; &iquest;Necesitas m&aacute;s? Ampl&iacute;a sin cambiar de plan.</span></div>
            <div class="price-addons-grid">
                <div class="addon-card">
                    <div class="addon-top">
                        <div class="addon-icon">&#x1F4E1;</div>
                        <div class="addon-name">Mayor alcance</div>
                    </div>
                    <p class="addon-desc">Llega a m&aacute;s clientes en cada env&iacute;o. Paga solo cuando lo necesitas, sin suscripci&oacute;n adicional.</p>
                    <a href="{{ $appUrl }}/register?source=precios-alcance" class="addon-btn">Ver tarifas</a>
                </div>
                <div class="addon-card">
                    <div class="addon-top">
                        <div class="addon-icon">&#x1F37D;</div>
                        <div class="addon-name">Carta PRO</div>
                    </div>
                    <p class="addon-desc">Carta digital con QR para tu negocio. Men&uacute; actualizable en tiempo real. M&oacute;dulo independiente.</p>
                    <a href="{{ $appUrl }}/register?source=precios-carta" class="addon-btn">Saber m&aacute;s</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CTA FINAL SPLIT --}}
<div class="cta-final-split">
    <div class="cta-left">
        <div class="cta-left-inner">
            <div class="eyebrow" style="color:#c4b5fd;">Empieza hoy</div>
            <h2>Más clientes.<br>Desde ya.</h2>
            <p>Regístrate gratis y empieza a captar clientes hoy mismo. Sin contratos, sin letra pequeña. El plan gratuito incluye todo lo que necesitas.</p>
            <div class="cta-btns">
                <a href="{{ $appUrl }}/register?source=web-cta-final" class="btn btn-accent">&#x1F464; Registrarme gratis</a>
                <a href="{{ url('/como-funciona') }}" class="btn" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.8);border:1.5px solid rgba(255,255,255,.25);">Cómo funciona</a>
            </div>
        </div>
    </div>
    <div class="cta-right" style="background:#fce8f3;">
        <img src="{{ asset('images/big/ecosistema-ev-comercios-clientes-asociacion-diagrama.png') }}"
             alt="Diagrama ecosistema Eventify: comercios, clientes y asociación conectados"
             loading="lazy" style="object-fit:contain;padding:2rem;">
        <div class="cta-right-ov"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function swTab(btn, id) {
    document.querySelectorAll('.tab-btn').forEach(function(t) { t.classList.remove('on'); });
    btn.classList.add('on');
    document.querySelectorAll('.bene-tab-panel').forEach(function(p) { p.classList.remove('on'); });
    document.getElementById(id).classList.add('on');
}
</script>
@endpush
