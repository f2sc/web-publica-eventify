@extends('layouts.app')

@section('content')

<div class="sub-hero">
    <div class="sub-hero-bg" style="background-image:url('{{ asset('images/big/cafeteria-barrio-qr-captacion-clientes-eventify.png') }}');background-position:center 30%;"></div>
    <div class="sub-hero-ov"></div>
    <div class="sub-hero-cnt">
        <div class="sub-ey">El proceso</div>
        <h1>Cómo funciona Eventify</h1>
        <p>De cero a tu primer cliente captado en menos de 10 minutos.</p>
    </div>
</div>

{{-- FLOW — 5 pasos numerados --}}
<section class="section" style="background:#fff;">
    <div class="container">
        <div class="eyebrow">Paso a paso</div>
        <h2 class="section-title">Más sencillo de lo que crees</h2>
        <div class="flow-list">
            <div class="flow-item">
                <div class="flow-num">1</div>
                <div class="flow-body">
                    <h3>Regístrate y personaliza tu perfil</h3>
                    <p>Crea tu cuenta en <strong>app.eventify.es</strong> en minutos. Añade el nombre de tu negocio, tu categoría y una descripción. Es el perfil público que verán tus clientes cuando escaneen tu QR.</p>
                </div>
            </div>
            <div class="flow-item">
                <div class="flow-num">2</div>
                <div class="flow-body">
                    <h3>Descarga e imprime tu cartel QR</h3>
                    <p>Eventify genera automáticamente un cartel con tu QR único. Lo descargas, lo imprimes con tu propia impresora y lo colocas en el mostrador. Sin copisterías, sin esperas.</p>
                </div>
            </div>
            <div class="flow-item">
                <div class="flow-num">3</div>
                <div class="flow-body">
                    <h3>Anima a cada cliente a escanearlo</h3>
                    <p>El cliente abre la cámara del móvil, apunta al QR y rellena un formulario web en 30 segundos. No descarga ninguna app. Tú ves cada registro en tiempo real en tu panel.</p>
                </div>
            </div>
            <div class="flow-item">
                <div class="flow-num">4</div>
                <div class="flow-body">
                    <h3>Crea y envía campañas desde el panel</h3>
                    <p>Escribe tu mensaje, elige si va a todos o a un segmento, y dale a enviar. Llega como notificación push y como email. En menos de 2 minutos, enviado.</p>
                </div>
            </div>
            <div class="flow-item">
                <div class="flow-num">5</div>
                <div class="flow-body">
                    <h3>Mide los resultados y consigue que vuelvan</h3>
                    <p>Ve cuántos clientes abrieron tu mensaje, cuántos son nuevos esta semana, qué campañas funcionan mejor. Datos reales para tomar mejores decisiones y fidelizar más.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- DEMO INTERACTIVA --}}
<section class="demo-sec">
    <div class="demo-header">
        <div class="eyebrow">Así se ve por dentro</div>
        <h2 class="section-title">Míralo tú mismo</h2>
        <p>Capturas reales del panel. Toca cada paso para verlo en acción.</p>
    </div>
    <div class="demo-tabs">
        <button class="demo-tab on" onclick="demoStep(this,0)"><span class="demo-tab-num">1</span> Registro</button>
        <button class="demo-tab" onclick="demoStep(this,1)"><span class="demo-tab-num">2</span> Tu QR</button>
        <button class="demo-tab" onclick="demoStep(this,2)"><span class="demo-tab-num">3</span> Captaci&oacute;n</button>
        <button class="demo-tab" onclick="demoStep(this,3)"><span class="demo-tab-num">4</span> Campa&ntilde;a</button>
        <button class="demo-tab" onclick="demoStep(this,4)"><span class="demo-tab-num">5</span> Resultados</button>
    </div>
    <div class="demo-panel">
        <div class="demo-step on" id="dstep-0">
            <div class="demo-visual"><img src="{{ asset('images/big/demo-paso-1-registro.png') }}" alt="Registro del comercio en Eventify" class="demo-img" loading="lazy"></div>
            <div class="demo-copy">
                <h3>Te registras en 2 minutos</h3>
                <p>Sin formularios kilométricos. Nombre, categoría, dirección y listo. Eventify hace el resto.</p>
                <div class="demo-copy-bullets">
                    <div class="demo-bullet"><div class="demo-bullet-ico">&#x2713;</div><span>Tu p&aacute;gina p&uacute;blica se crea autom&aacute;ticamente</span></div>
                    <div class="demo-bullet"><div class="demo-bullet-ico">&#x2713;</div><span>Sin instalar nada ni pedir ayuda t&eacute;cnica</span></div>
                    <div class="demo-bullet"><div class="demo-bullet-ico">&#x2713;</div><span>Gratuito desde el primer d&iacute;a</span></div>
                </div>
                <a href="{{ $appUrl }}/register?source=como-funciona-demo" class="btn btn-primary" style="margin-top:1.5rem;">Registrarme ahora</a>
            </div>
        </div>
        <div class="demo-step" id="dstep-1">
            <div class="demo-visual"><img src="{{ asset('images/big/demo-paso-2-qr.png') }}" alt="Descarga de cartel QR en Eventify" class="demo-img" loading="lazy"></div>
            <div class="demo-copy">
                <h3>Tu QR listo para imprimir</h3>
                <p>Eventify genera tu cartel automáticamente. Lo descargas, lo imprimes con tu propia impresora y lo colocas donde más lo vean.</p>
                <div class="demo-copy-bullets">
                    <div class="demo-bullet"><div class="demo-bullet-ico">&#x2713;</div><span>Cartel A4 y QR de mesa incluidos</span></div>
                    <div class="demo-bullet"><div class="demo-bullet-ico">&#x2713;</div><span>Tambi&eacute;n puedes pedir el cartel f&iacute;sico</span></div>
                    <div class="demo-bullet"><div class="demo-bullet-ico">&#x2713;</div><span>El QR es &uacute;nico para tu comercio</span></div>
                </div>
            </div>
        </div>
        <div class="demo-step" id="dstep-2">
            <div class="demo-visual"><img src="{{ asset('images/big/demo-paso-3-captacion.png') }}" alt="Cliente escaneando QR en comercio local" class="demo-img" loading="lazy"></div>
            <div class="demo-copy">
                <h3>Tus clientes se registran en 30 segundos</h3>
                @if(!empty($comercioDemo))
                <p>Así lo hace <strong>{{ $comercioDemo['nombre_comercial'] }}</strong>{{ !empty($comercioDemo['localidad']['nombre']) ? ' en ' . $comercioDemo['localidad']['nombre'] : '' }}. El cliente escanea, rellena nombre y móvil, y queda registrado. Tú lo ves al instante en tu panel.</p>
                @else
                <p>El cliente abre la cámara, escanea tu QR y ve tu página. Rellena nombre y móvil — sin descargar ninguna app. Tú ves cada registro al instante.</p>
                @endif
                <div class="demo-copy-bullets">
                    <div class="demo-bullet"><div class="demo-bullet-ico">&#x2713;</div><span>Sin app, desde cualquier m&oacute;vil</span></div>
                    <div class="demo-bullet"><div class="demo-bullet-ico">&#x2713;</div><span>Consentimiento RGPD autom&aacute;tico</span></div>
                    <div class="demo-bullet"><div class="demo-bullet-ico">&#x2713;</div><span>Cada escaneo suma un contacto directo</span></div>
                </div>
            </div>
        </div>
        <div class="demo-step" id="dstep-3">
            <div class="demo-visual"><img src="{{ asset('images/big/demo-paso-4-campana.png') }}" alt="Envío de campaña desde el panel Eventify" class="demo-img" loading="lazy"></div>
            <div class="demo-copy">
                <h3>Campaña enviada en 2 minutos</h3>
                <p>Escribes el mensaje, eliges si va a todos o a un segmento, y le das a enviar. Llega como push al móvil y como email.</p>
                <div class="demo-copy-bullets">
                    <div class="demo-bullet"><div class="demo-bullet-ico">&#x2713;</div><span>Push + email en un solo clic</span></div>
                    <div class="demo-bullet"><div class="demo-bullet-ico">&#x2713;</div><span>Segmenta por zona o &uacute;ltima visita</span></div>
                    <div class="demo-bullet"><div class="demo-bullet-ico">&#x2713;</div><span>Automatiza cumplea&ntilde;os y bienvenidas</span></div>
                </div>
            </div>
        </div>
        <div class="demo-step" id="dstep-4">
            <div class="demo-visual"><img src="{{ asset('images/big/demo-paso-5-resultados.png') }}" alt="Resultados y estadísticas en Eventify" class="demo-img" loading="lazy"></div>
            <div class="demo-copy">
                <h3>Ves lo que funciona, en tiempo real</h3>
                <p>Cu&aacute;ntos clientes tienes, qu&eacute; campa&ntilde;a tuvo m&aacute;s aperturas, cu&aacute;ntos son nuevos esta semana. Sin Excel, sin conjeturas.</p>
                <div class="demo-copy-bullets">
                    <div class="demo-bullet"><div class="demo-bullet-ico">&#x2713;</div><span>Tasa de apertura por campa&ntilde;a</span></div>
                    <div class="demo-bullet"><div class="demo-bullet-ico">&#x2713;</div><span>Nuevos clientes por semana</span></div>
                    <div class="demo-bullet"><div class="demo-bullet-ico">&#x2713;</div><span>Exporta tus contactos cuando quieras</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FAQ — grid 2×2 --}}
<section class="section" style="background:#f9fafb;">
    <div class="container">
        <div class="eyebrow" style="justify-content:center;">FAQ</div>
        <h2 class="section-title" style="text-align:center;">Preguntas frecuentes</h2>
        <div class="faq-grid">
            <div class="faq-card">
                <h3>&#x1F4F1; ¿Necesitan mis clientes instalar una app?</h3>
                <p>No. El cliente escanea el QR con la cámara del móvil, rellena un formulario web y listo. Sin descargas, sin registros en tiendas de apps.</p>
            </div>
            <div class="faq-card">
                <h3>&#x1F6E1; ¿Es legal recoger datos de clientes?</h3>
                <p>Sí. El cliente da su consentimiento explícito al registrarse. Eventify gestiona el cumplimiento del RGPD automáticamente.</p>
            </div>
            <div class="faq-card">
                <h3>&#x1F514; ¿Qué tipo de notificaciones puedo enviar?</h3>
                <p>Push al navegador, emails, o ambos a la vez. Ofertas, eventos, felicitaciones de cumpleaños automáticas, recordatorios de visita...</p>
            </div>
            <div class="faq-card">
                <h3>&#x23F1; ¿Cuánto tarda en estar listo?</h3>
                <p>Menos de 10 minutos desde el registro hasta tener el QR descargado y listo para imprimir. Hoy mismo empiezas a captar clientes.</p>
            </div>
        </div>
    </div>
</section>

{{-- CTA FINAL --}}
<div class="cta-final-split">
    <div class="cta-left">
        <div class="cta-left-inner">
            <h2>¿Listo para probarlo?</h2>
            <p>Gratis. Sin tarjeta. Sin compromisos.</p>
            <div class="cta-btns">
                <a href="{{ $appUrl }}/register?source=como-funciona-cta" class="btn btn-accent">&#x1F4F1; Registrarme gratis</a>
            </div>
        </div>
    </div>
    <div class="cta-right">
        <img src="{{ asset('images/big/comerciante-tienda-eventify-dashboard-qr.png') }}"
             alt="Comerciante usando Eventify en su tienda" loading="lazy">
        <div class="cta-right-ov"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function demoStep(btn, idx) {
    document.querySelectorAll('.demo-tab').forEach(function(t) { t.classList.remove('on'); });
    btn.classList.add('on');
    document.querySelectorAll('.demo-step').forEach(function(s) { s.classList.remove('on'); });
    var step = document.getElementById('dstep-' + idx);
    if (step) step.classList.add('on');
}
</script>
@endpush

@push('head')
<style>
/* Flow — 5 pasos numerados */
.flow-list { display: flex; flex-direction: column; max-width: 780px; margin: 3rem auto 0; }
.flow-item { display: flex; align-items: flex-start; gap: 2rem; padding: 2rem 0; border-bottom: 1px solid #e5e7eb; }
.flow-item:last-child { border-bottom: none; }
.flow-num { width: 48px; height: 48px; border-radius: 50%; background: var(--grad-brand); color: #fff; font-size: 1.1rem; font-weight: 900; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.flow-body h3 { font-size: 1.1rem; font-weight: 800; color: var(--navy); margin-bottom: 0.4rem; }
.flow-body p { color: #6b7280; font-size: 0.95rem; line-height: 1.65; margin: 0; }

/* FAQ grid 2×2 */
.faq-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin: 2.5rem auto 0; max-width: 900px; }
.faq-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 2rem; transition: box-shadow .2s; }
.faq-card:hover { box-shadow: 0 6px 24px rgba(109,0,126,.09); }
.faq-card h3 { font-size: 1rem; font-weight: 800; color: var(--navy); margin-bottom: 0.75rem; }
.faq-card p { color: #6b7280; font-size: 0.9rem; line-height: 1.65; margin: 0; }

@media(max-width:768px){
    .flow-item { gap: 1.25rem; }
    .faq-grid { grid-template-columns: 1fr; }
}
</style>
@endpush
