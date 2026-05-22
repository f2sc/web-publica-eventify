@extends('layouts.app')

@section('content')

<div class="sub-hero">
    <div class="sub-hero-bg" style="background-image:url('{{ asset('images/big/comerciante-movil-tienda-local-eventify.png') }}');background-position:center center;"></div>
    <div class="sub-hero-ov"></div>
    <div class="sub-hero-cnt">
        <div class="sub-ey">Para comercios</div>
        <h1>Tu negocio conectado<br>con tu barrio.</h1>
        <p>La herramienta de fidelización más sencilla del mercado. Sin contratos.</p>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:1.5rem;">
            <a href="{{ $appUrl }}/register?source=para-comercios" class="btn btn-accent">Crear mi cuenta gratis</a>
            <a href="{{ url('/como-funciona') }}" class="btn" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.88);border:1.5px solid rgba(255,255,255,.28);">Cómo funciona</a>
        </div>
    </div>
</div>

<section class="section" style="background:#fff;">
    <div class="container">
        <div class="feat-split">
            <div>
                <div class="eyebrow">Tu panel de control</div>
                <h2 class="section-title">Todo lo que necesitas<br>en un solo lugar.</h2>
                <p style="color:#6b7280;margin-bottom:1.5rem;">Panel en español, pensado para el dueño del comercio, no para un informático.</p>
                <ul class="feat-list">
                    <li>
                        <div class="feat-li-icon">&#x1F5C3;</div>
                        <div><strong>Base de datos de clientes</strong> — Cada escaneo suma un contacto a tu lista. Exportable.</div>
                    </li>
                    <li>
                        <div class="feat-li-icon">&#x2709;</div>
                        <div><strong>Campañas en 2 minutos</strong> — Escribe, elige el segmento, envía. Así de simple.</div>
                    </li>
                    <li>
                        <div class="feat-li-icon">&#x1F4CA;</div>
                        <div><strong>Analítica real</strong> — Tasa de apertura, clics, nuevos clientes por semana.</div>
                    </li>
                    <li>
                        <div class="feat-li-icon">&#x1F382;</div>
                        <div><strong>Automatizaciones</strong> — Cumpleaños, bienvenida, recordatorios. Se configuran una vez.</div>
                    </li>
                    <li>
                        <div class="feat-li-icon">&#x1F4F1;</div>
                        <div><strong>Cartel QR descargable</strong> — Generado automáticamente. Listo para imprimir.</div>
                    </li>
                </ul>
            </div>
            <div class="feat-split-img">
                <img src="{{ asset('images/big/panel-control-dashboard-eventify-comercio.png') }}"
                     alt="Panel de control Eventify — dashboard del comercio con QR, notificaciones y base de datos de clientes"
                     loading="lazy">
            </div>
        </div>
    </div>
</section>

{{-- SLIDER DE COMERCIOS --}}
@if(count($comercios) > 0)
<section class="com-slider-sec">
    <div class="com-slider-header">
        <div class="eyebrow">&#x1F3EA; Comercios reales</div>
        <h2 class="section-title">Ya confían en Eventify</h2>
        <p>{{ isset($stats['comercios']) ? $stats['comercios'] . ' comercios locales fidelizan' : 'Comercios locales que fidelizan' }} a sus clientes con Eventify.</p>
    </div>
    <div class="com-slider-wrap">
        <div class="com-slider-track" id="comTrack">
            @foreach($comercios as $c)
            @php
                $cNombre  = $c['nombre_comercial'] ?? '';
                $cCat     = $c['categoria']['nombre'] ?? '';
                $cLoc     = $c['localidad']['nombre'] ?? '';
                $cLogo    = $c['url_logo'] ?? null;
                $cCodigo  = $c['codigo_comercio'] ?? ($c['slug'] ?? null);
                $cInits   = strtoupper(mb_substr($cNombre, 0, 2, 'UTF-8'));
                $cGrads   = ['linear-gradient(135deg,#6d007e,#b12140)','linear-gradient(135deg,#b12140,#6d007e)','linear-gradient(135deg,#9d1060,#b12140)','linear-gradient(135deg,#6d007e,#9d1060)'];
                $cGrad    = $cGrads[abs(crc32($cNombre)) % count($cGrads)];
            @endphp
            @if($cCodigo)
            <a href="{{ $appUrl }}/c/{{ $cCodigo }}" class="com-card" target="_blank" rel="noopener">
            @else
            <div class="com-card">
            @endif
                @if($cLogo)
                <div class="com-card-logo" style="background:#f5f5f5;padding:0;overflow:hidden;"><img src="{{ $cLogo }}" alt="{{ $cNombre }}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;"></div>
                @else
                <div class="com-card-logo" style="background:{{ $cGrad }};">{{ $cInits }}</div>
                @endif
                <div>
                    <div class="com-card-name">{{ $cNombre }}</div>
                    @if($cCat)<div class="com-card-type">{{ $cCat }}</div>@endif
                    @if($cLoc)<div class="com-card-loc">{{ $cLoc }}</div>@endif
                </div>
            @if($cCodigo)
            </a>
            @else
            </div>
            @endif
            @endforeach
        </div>
    </div>
</section>
@endif

<div class="cta-final-split">
    <div class="cta-left">
        <div class="cta-left-inner">
            <h2>Crea tu QR gratis<br>en 10 minutos.</h2>
            <p>Sin tarjeta de crédito. Sin compromisos. El plan gratuito incluye todo lo que necesitas para empezar.</p>
            <div class="cta-btns">
                <a href="{{ $appUrl }}/register?source=para-comercios-cta" class="btn btn-accent">Registrarme gratis &rarr;</a>
            </div>
        </div>
    </div>
    <div class="cta-right">
        <img src="{{ asset('images/big/comerciante-movil-tienda-local-eventify.png') }}"
             alt="Comerciante gestionando Eventify desde su móvil" loading="lazy" style="object-position:center 15%;">
        <div class="cta-right-ov"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function() {
    var track = document.getElementById('comTrack');
    if (!track) return;
    track.innerHTML = track.innerHTML + track.innerHTML;
})();
</script>
@endpush

@push('head')
<style>
.feat-split { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; }
.feat-split-img img { width: 100%; max-width: 560px; border-radius: 12px; filter: drop-shadow(0 20px 48px rgba(82,63,105,.18)); }
.feat-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem; }
.feat-list li { display: flex; gap: 0.85rem; align-items: flex-start; font-size: 0.95rem; color: #374151; }
.feat-li-icon { font-size: 1.25rem; flex-shrink: 0; line-height: 1.4; }
.feat-list strong { color: var(--navy); }
@media(max-width:900px){ .feat-split { grid-template-columns: 1fr; gap: 2rem; } .feat-split-img { display: flex; justify-content: center; } }
</style>
@endpush
